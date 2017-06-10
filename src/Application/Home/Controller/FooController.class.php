<?php
/**
 * Created by PhpStorm.
 * User: xiedalie
 * Date: 2016/11/25
 * Time: 15:58
 */

namespace Home\Controller;


use Home\Model\WxBiz;
use Think\Controller;
use Vendor\Hiland\Biz\Tencent\WechatHelper;
use Vendor\Hiland\Utils\IO\DirHelper;
use Vendor\Hiland\Utils\IO\ImageHelper;
use Vendor\Hiland\Utils\Office\ExcelHelper;
use Vendor\Hiland\Utils\Web\NetHelper;

class FooController extends Controller
{
    public function index()
    {
        dump(1111111111);
    }

    public function wxbiz()
    {
        WxBiz::createQrcode(3, "oinMwxGi-Ok20PEf5lUn6TtPaQXg");
    }

    public function wximg()
    {
        $headimgurl = "http://wx.qlogo.cn/mmopen/Ib5852jAybibhPd6DV1FzXCgLicqMreYh8LTWtFje4ePscFDPl8KMc2jAo65z5IjNluaQBBwkIVS2oxX67eqFBaoRnjoesVAWL/0";
//        $data = NetHelper::get($headimgurl);
//        dump($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_URL, $headimgurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $headimg = curl_exec($ch);
        curl_close($ch);
        dump($headimg);
    }

    public function uploadimg()
    {
        $wechat = WxBiz::getWechat();

        $file = PHYSICAL_ROOT_PATH . "\\QRcode\\promotion\\oinMwxGi-Ok20PEf5lUn6TtPaQXg.jpg";
        dump($file);
        $data = array('media' => '@' . $file);
        $result = $wechat->uploadMedia($data, 'image');
        dump($result);

        $rt = WechatHelper::uploadMedia($file);
        dump($rt);
    }

    public function wxav()
    {
        $hostName = "http://wx.qlogo.cn";

        $ip = C('WX_AVATARSERVER_IP');
        $hostName = "http://$ip";
        $recommenduseravatar = "$hostName/mmopen/Ib5852jAybibhPd6DV1FzXCgLicqMreYh8LTWtFje4ePscFDPl8KMc2jAo65z5IjNluaQBBwkIVS2oxX67eqFBaoRnjoesVAWL/0";

        //$headimg = ImageHelper::loadImage($recommenduseravatar, 'non');

        $headimg = NetHelper::request($recommenduseravatar, null, 30);
        //$headimg= NetHelper::get($recommenduseravatar,true);
        //$headimg= $this-> ss($recommenduseravatar);

        $headimg = imagecreatefromstring($headimg);
        ImageHelper::display($headimg);
        //dump($headimg);
    }

    public function jsop()
    {
        $this->display();
    }

    public function dirop()
    {
        $path = "E:\\aa\\bb\\cc\\dd";
//        if(is_dir($path)==false){
//            mkdir($path);
//        }

        DirHelper::surePathExist($path);
    }

    public function aa()
    {
        dump('http://' . $_SERVER['HTTP_HOST'] . __ROOT__ . '/index.php/Home/Wxpay/nd/');
    }

    public function ems()
    {
        if (IS_POST) {
//            error_reporting(E_ALL);
//            ini_set('display_errors', TRUE);
//            ini_set('display_startup_errors', TRUE);
//            date_default_timezone_set('Asia/Shanghai');

            $physicalRootPath = PHYSICAL_ROOT_PATH;
            //dump($physicalRootPath);
            $phpExcelPath= $physicalRootPath. '/ThinkPHP/Library/Vendor/PHPExcel';
            //dump($phpExcelPath);

            $originalExcel = $physicalRootPath . '\Upload\EMS\original.xlsx';
            //dump($originalExcel);
            if (!file_exists($originalExcel)) {
                exit("请确保以下文件存在：" . $originalExcel);
            }

//            //require_once $phpExcelPath. '/PHPExcel/IOFactory.php';
//            $exceFactory= 'E:/myworkspace/project-php/xiedadan/src/ThinkPHP/Library/Vendor/PHPExcel/PHPExcel/IOFactory.php';
//            require_once $exceFactory;
//
//            //require_once (dirname(__FILE__) . '\\autoload.php');
//
//            //dump(get_included_files());
//
//            $originalExcel= $physicalRootPath .'\Upload\EMS\original.xlsx';
//            //dump($originalExcel);
//            if (!file_exists($originalExcel)) {
//                exit("请确保以下文件存在：".$originalExcel);
//            }
//            $objPHPExcel= \PHPExcel_IOFactory::load($originalExcel);
//
//
//            $sheet = $objPHPExcel->getSheet(0);
//
//            //获取行数与列数,注意列数需要转换
//            $highestRowNum = $sheet->getHighestRow();
//            $highestColumn = $sheet->getHighestColumn();
//            $highestColumnNum = \PHPExcel_Cell::columnIndexFromString($highestColumn);
//
//            //取得字段，这里测试表格中的第一行为数据的字段，因此先取出用来作后面数组的键名
//            $filed = array();
//            for ($i = 0; $i < $highestColumnNum; $i++) {
//                $cellName = \PHPExcel_Cell::stringFromColumnIndex($i) . '1';
//                $cellVal = $sheet->getCell($cellName)->getValue();//取得列内容
//                $filed [] = $cellVal;
//            }
//
//            //开始取出数据并存入数组
//            $data = array();
//            for ($i = 2; $i <= $highestRowNum; $i++) {//ignore row 1
//                $row = array();
//                for ($j = 0; $j < $highestColumnNum; $j++) {
//                    $cellName = \PHPExcel_Cell::stringFromColumnIndex($j) . $i;
//                    $cellVal = $sheet->getCell($cellName)->getValue();
//                    $row[$filed[$j]] = $cellVal;
//                }
//                $data [] = $row;
//            }

            $data =ExcelHelper::getSheetContent($originalExcel,0,1);
            dump($data);


//            //dump($data);
//            $excelWriter = new \PHPExcel();
//            //设置基本信息
//            $excelWriter->getProperties()->setCreator("jecken")
//                ->setLastModifiedBy("jecken")
//                ->setTitle("上海**人力资源服务有限公司")
//                ->setSubject("简历列表")
//                ->setDescription("")
//                ->setKeywords("简历列表")
//                ->setCategory("");
//            $excelWriter->setActiveSheetIndex(0);
//            $excelWriter->getActiveSheet()->setTitle("ssss");
//            $excelWriter->getActiveSheet()->setCellValue('D3', '一个好人');
//            $excelWriter->getActiveSheet()->setCellValue('E4', 100);
//
//
//            //保存为2003格式
//            $objWriter = new \PHPExcel_Writer_Excel5 ($excelWriter);
//            //多浏览器下兼容中文标题
//            $fileName= "保持稳健";
//            $encoded_filename = urlencode($fileName);
//            $ua = $_SERVER["HTTP_USER_AGENT"];
//            if (preg_match("/MSIE/", $ua)) {
//                header('Content-Disposition: attachment; filename="' . $encoded_filename . '.xls"');
//            } else if (preg_match("/Firefox/", $ua)) {
//                header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileName . '.xls"');
//            } else {
//                header('Content-Disposition: attachment; filename="' . $fileName . '.xls"');
//            }
//
//            header("Content-Transfer-Encoding:binary");
//            $objWriter->save('php://output');
        } else {

            $this->display();
        }
    }

    private function ss($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }
}