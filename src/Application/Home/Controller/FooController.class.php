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
use Vendor\Hiland\Utils\Data\StringHelper;
use Vendor\Hiland\Utils\IO\DirHelper;
use Vendor\Hiland\Utils\IO\ImageHelper;
use Vendor\Hiland\Utils\Office\ExcelHelper;
use Vendor\Hiland\Utils\Web\NetHelper;
use Vendor\Hiland\Utils\Web\WebHelper;

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

    public function danmatch()
    {
        if (IS_POST) {
            $ourhao = $_POST['ourhao'];
            $ourhao = str2arr($ourhao, PHP_EOL);
            //$ourhao= array_flip($ourhao);
            //dump($ourhao);

            $emshao = $_POST['emshao'];
            $emshao = str2arr($emshao, PHP_EOL);
            $emshao = array_flip($emshao);
            //array_key_exists
            //dump($emshao);

            foreach ($ourhao as $outitem) {
                //dump($outitem);
                if (array_key_exists($outitem, $emshao)) {
                    //pass
                } else {
                    dump($outitem);
                }
            }


            $this->display();
        } else {
            $this->display();
        }
    }

    public function ems()
    {
        if (IS_POST) {
            $physicalRootPath = PHYSICAL_ROOT_PATH;
            //dump($physicalRootPath);
            $phpExcelPath = $physicalRootPath . '/ThinkPHP/Library/Vendor/PHPExcel';
            //dump($phpExcelPath);

//            $originalExcel = $physicalRootPath . '\Upload\EMS\original.xlsx';
//            //dump($originalExcel);
//            if (!file_exists($originalExcel)) {
//                exit("请确保以下文件存在：" . $originalExcel);
//            }

            $emsFee = C('EMS_FEE');

            $yundanhao = $_POST['yundanhao'];
            $yundanhao = str2arr($yundanhao, PHP_EOL);
            //dump($yundanhao);
            $yundanhao = array_flip($yundanhao);
            //dump($yundanhao);

            $originalFile = $_FILES['originalFile'];
            //dump($originalFile);
            $originalExcel = $originalFile['tmp_name'];
            if (!empty($originalExcel)) {
                $data = ExcelHelper::getSheetContent($originalExcel, 0, 1);

                $newData = array();
                foreach ($data as $item) {
                    if (array_key_exists($item['寄达省份'], $emsFee)) {
                        $item['UnitFee'] = $emsFee[$item['寄达省份']];
                    }

                    //dump($item['邮件号']);
                    if (array_key_exists($item['邮件号'], $yundanhao)) {
                        $item['AB'] = 'B';
                    } else {
                        $item['AB'] = 'A';
                    }

                    $newData[] = $item;
                }

                ExcelHelper::download($newData);
                //dump($newData);
            }

            $this->display();
        } else {
            $this->display();
        }
    }

    public function uc()
    {
        if (IS_POST) {
            $emsFee = C('EMS_FEE');

            $yundanhao = $_POST['yundanhao'];
            $yundanhao = str2arr($yundanhao, PHP_EOL);
            //dump($yundanhao);
            $yundanhao = array_flip($yundanhao);
            //dump($yundanhao);

            $originalFile = $_FILES['originalFile'];
            //dump($originalFile);
            $originalExcel = $originalFile['tmp_name'];
            if (!empty($originalExcel)) {
                $data = ExcelHelper::getSheetContent($originalExcel, 0, 1);

                $newData = array();
                foreach ($data as $item) {
                    if (array_key_exists($item['寄达省份'], $emsFee)) {
                        $item['UnitFee'] = $emsFee[$item['寄达省份']];
                    }

                    //dump($item['邮件号']);
                    if (array_key_exists($item['邮件号'], $yundanhao)) {
                        $item['AB'] = 'B';
                    } else {
                        $item['AB'] = 'A';
                    }

                    $newData[] = $item;
                }

                ExcelHelper::download($newData);
            }

            $this->display();
        } else {
            $this->display();
        }
    }


    public function down()
    {

//        $file = fopen("a.txt","r");
//        WebHelper::download($file,"ok.txt");

        WebHelper::download("a.txt", "ok.txt");
//        header("Content-type:application/octet-stream");
//        header("Accept-Ranges:bytes");
//        header("Content-Disposition:attachment;filename=".'id列表_'.date("YmdHis").".txt");
//        header("Expires: 0");
//        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
//        header("Pragma:public");
//        file_put_contents("php://output","hello,world!");
    }

    /**
     * 为163邮箱导出的通讯录添加“N:;XXX;;;”字段
     */
    public function vcard()
    {
        if (IS_POST) {
            $physicalRootPath = PHYSICAL_ROOT_PATH;

            $originalFile = $_FILES['originalFile'];

            $file = fopen($originalFile["tmp_name"], "r");

            $user = array();
            $i = 0;
            //输出文本中所有的行，直到文件结束为止。
            while (!feof($file)) {
                $temp= fgets($file);//fgets()函数从文件指针中读取一行
                $user[$i] = $temp;
                $i++;
                if(StringHelper::isStartWith($temp,"FN:")){
                    $realName= StringHelper::getSeperatorAfterString($temp,"FN:");
                    $realName= StringHelper::getSeperatorBeforeString($realName,"\r\n");

                    $user[$i] = "N:;". $realName.";;;\r\n";
                    $i++;
                }
            }
            fclose($file);
            //$user = array_filter($user);


            WebHelper::download($user,"newuser.vcf");
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