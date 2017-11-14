<?php
/**
 * Created by PhpStorm.
 * User: xiedalie
 * Date: 2017/11/9
 * Time: 9:21
 */

namespace Admin\Controller;


use Vendor\Hiland\Utils\Data\GuidHelper;
use Vendor\Hiland\Utils\Data\StringHelper;
use Vendor\Hiland\Utils\DataModel\ModelMate;
use Vendor\Hiland\Utils\Office\ExcelHelper;
use Vendor\Hiland\Utils\Web\WebHelper;

class CrmController extends BaseController
{
    public function index()
    {
        $entity = M('crm_coustombasic'); // 实例化User对象
        $count = $entity->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($count, 12);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig('theme', "<div class='widget-content padded text-center'><ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul></div>");
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $entity = $entity->limit($Page->firstRow . ',' . $Page->listRows)->order("id desc")->select();

        $this->assign("entity", $entity);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display(); // 输出模板
    }

    //在以txt格式存在的多行（每行一个手机号码）文本中，去除重复的电话号码
    public function quchongfu()
    {
        if (IS_POST) {
            $originalFile = $_FILES['uploadfile'];
            //dump($originalFile);
            $originalExcel = $originalFile['tmp_name'];

            $newBasicCount = 0;
            $existBasicCount = 0;
            $newTradeCount = 0;

            $file = file($originalExcel);

            $newData = array();
            foreach ($file as &$line) {
                $newData[$line] = $line;
            }

            WebHelper::download($newData);
        } else {
            $this->display();
        }
    }

    public function ex2vcf()
    {
        if (IS_POST) {
            $beginNo = $_POST['beginNo'];
            $endNo = $_POST['endNo'];

            $basicMate = new ModelMate("crm_coustombasic");

            $condition['id'] = array('EGT', $beginNo);
            $map['_complex'] = $condition;
            $map['id'] = array('LT', $endNo);
            $targetData = $basicMate->select($map);

            $itemOut = array();
            foreach ($targetData as $item) {
                $itemOut[] = "BEGIN:VCARD\r\n";
                $itemOut[] = "VERSION:3.0\r\n";
                $itemOut[] = "N:;" . $item["name"] . ";;;\r\n";
                $itemOut[] = "FN:" . $item["name"] . "\r\n";
                $itemOut[] = "TEL;TYPE=mobile:" . $item["telephone"] . "\r\n";
                $itemOut[] = "TEL;TYPE=mobile:" . $item["telephone2"] . "\r\n";
                $itemOut[] = "ORG:" . $item["comefrom"] . "\r\n";
                $itemOut[] = "END:VCARD" . "\r\n" . "\r\n";
            }

            WebHelper::download($itemOut);
        } else {
            $this->display();
        }
    }

    public function import()
    {
        //处理POST提交
        if (IS_POST) {
            $originalFile = $_FILES['uploadfile'];
            //dump($originalFile);
            $originalExcel = $originalFile['tmp_name'];

            $newBasicCount = 0;
            $existBasicCount = 0;
            $newTradeCount = 0;
            if (!empty($originalExcel)) {
                $data = ExcelHelper::getSheetContent($originalExcel, 0, 1);

                $newData = array();
                $basicMate = new ModelMate("crm_coustombasic");
                $tradeMate = new ModelMate("crm_trade");

                foreach ($data as $item) {
                    if (empty($item['Customer Name'])) {
                        continue;
                    } else {
                        //===分隔电话号为两个电话=================================================
                        $phone = $item['Phone'];
                        $phoneA = StringHelper::getSeperatorBeforeString($phone, "/");
                        $phoneB = StringHelper::getSeperatorAfterString($phone, "/");

                        $phoneA = trim($phoneA);
                        $phoneB = trim($phoneB);

                        $item['phoneA'] = $phoneA;
                        $item['phoneB'] = $phoneB;
                        //=====================================================================
                        $where1['telephone'] = $phoneA;
                        $where1['telephone2'] = $phoneA;
                        $where1['_logic'] = 'or';

                        $where2['_complex'] = $where1;
                        $where2['telephone'] = $phoneB;
                        $where2['_logic'] = 'or';

                        $where['_complex'] = $where2;
                        $where['telephone2'] = $phoneB;
                        $where['_logic'] = 'or';

                        $map['_complex'] = $where;
                        $map['name'] = $item['Customer Name'];

                        $existData = $basicMate->find($map);

                        $tradeData = array();
                        $tradeData['tradedate'] = $item['Create Date'];
                        $tradeData['tradecontent'] = $item['Product Name'];
                        $tradeData['delivercompany'] = $item['Deliver Time'];
                        $tradeData['delivercode'] = $item['Tracking No'];

                        if ($existData) {
                            $existBasicCount++;
                            $newTradeCount++;

                            $existGuid = $existData['guid'];
                            $tradeData['customguid'] = $existGuid;
                            $existData['tradecount'] += 1;
                            $basicMate->interact($existData);
                        } else {
                            $newBasicCount++;
                            $newData = array();
                            $guid = GuidHelper::newGuid();

                            $newData["guid"] = $guid;
                            $tradeData['customguid'] = $guid;

                            $newData["name"] = $item['Customer Name'];
                            $newData["comefrom"] = "tps";
                            $newData["telephone"] = $phoneA;
                            $newData["telephone2"] = $phoneB;
                            $newData["address"] = $item['Address'];

                            $newData["tradecount"] = 1;
                            $basicMate->interact($newData);
                        }

                        $tradeMate->interact($tradeData);
                        //$newData[] = $item;
                    }
                }
            }

            $fileNameUploaded = $originalFile['name'];
            dump("刚才导入的文件为 $fileNameUploaded 。\r\n本次共导入新客户 $newBasicCount 个；系统已经存在的客户 $existBasicCount 个。");
            $this->display();

        } else {
            $this->display();
        }
    }
}