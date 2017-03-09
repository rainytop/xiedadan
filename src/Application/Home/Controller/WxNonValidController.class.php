<?php
/**
 * Created by PhpStorm.
 * User: xiedalie
 * Date: 2016/11/25
 * Time: 9:37
 */

namespace Home\Controller;


use App\QRcode;
use Home\Model\WxBiz;
use Think\Controller;
use Vendor\Hiland\Biz\Loger\CommonLoger;
use Vendor\Hiland\Biz\Tencent\WechatHelper;
use Vendor\Hiland\Utils\Web\NetHelper;

class WxNonValidController extends Controller
{
    public static $_wx; //缓存微信对象

    public function __construct($options)
    {
        self::$_wx = WxBiz::getWechat();
    }

    public function reply4Test($openid)
    {
        $accessToken = WechatHelper::getAccessToken();
        CommonLoger::log($openid, $accessToken);
        WechatHelper::responseCustomerServiceText($openid, "nihao");
    }

    /**
     * 对关键词“员工二维码”进行响应
     */
    public function reply4YuanGongErWeiMa($openid)
    {
        // 获取用户信息
        $map['openid'] = $openid;//self::$_revdata['FromUserName'];
        $vipModel = M('Vip');
        $vip = $vipModel->where($map)->find();

        // 用户校正
        if (!$vip) {
            $msg = "用户信息缺失，请重新关注公众号";
            WechatHelper::responseCustomerServiceText($openid, $msg);
            exit();
        }

        // 获取员工信息
        $employee = M('Employee')->where(array('vipid' => $vip['id']))->find();

        // 员工校正
        if (!$employee) {
            $msg = "抱歉，您不是员工，请先联系系统管理员！";
            WechatHelper::responseCustomerServiceText($openid, $msg);
            exit();
        }

        // 过滤连续请求-打开
        if (F("employee" . $vip['openid']) != null) {
            $msg = "员工二维码正在生成，请稍等！";
            WechatHelper::responseCustomerServiceText($openid, $msg);
            exit();
        } else {
            F("employee" . $vip['openid'], $vip['openid']);
        }

        // 生产二维码基本信息，存入本地文档，获取背景
        $background = WxBiz::createQrcodeBg4Employee();
        //$qrcode = $this->createQrcode($vip['id'],$vip['openid']);
        $qrcode = WxBiz::createQrcode4Employee($employee['id'], $vip['openid']);
        if (!$qrcode) {
            $msg = "员工二维码 生成失败";
            WechatHelper::responseCustomerServiceText($openid, $msg);
            F("employee" . $vip['openid'], null);
            exit();
        }
        // 生产二维码基本信息，存入本地文档，获取背景 结束

        // 获取头像信息
        $mark = false; // 是否需要写入将图片写入文件
        $imageUrl = $vip['headimgurl'];
        $wxAvatarIp = C('WX_AVATARSERVER_IP');
        if ($wxAvatarIp) {
            $imageUrl = str_replace('wx.qlogo.cn', $wxAvatarIp, $imageUrl);
        }
        $headimg = NetHelper::request($imageUrl);
        if (!$headimg) {// 没有头像先从头像库查找，再没有就选择默认头像
            if (file_exists('./QRcode/headimg/' . $vip['openid'] . '.jpg')) { // 获取不到远程头像，但存在本地头像，需要更新
                $headimg = file_get_contents('./QRcode/headimg/' . $vip['openid'] . '.jpg');
            } else {
                $headimg = file_get_contents('./QRcode/headimg/' . 'default' . '.jpg');
            }
            $mark = true;
        }
        $headimg = imagecreatefromstring($headimg);
        // 获取头像信息 结束

        // 生成二维码推广图片=======================

        // Combine QRcode and background and HeadImg
        $b_width = imagesx($background);
        $b_height = imagesy($background);
        $q_width = imagesx($qrcode);
        $q_height = imagesy($qrcode);
        $h_width = imagesx($headimg);
        $h_height = imagesy($headimg);
        imagecopyresampled($background, $qrcode, $b_width * 0.24, $b_height * 0.5, 0, 0, $q_width * 1.5, $q_height * 1.5, $q_width, $q_height);
        imagecopyresampled($background, $headimg, $b_width * 0.10, 12, 0, 0, 120, 120, $h_width, $h_height);

        // Set Font Type And Color
        $fonttype = './Public/Common/fonts/wqy-microhei.ttc';
        $fontcolor = imagecolorallocate($background, 0x00, 0x00, 0x00);

        // Combine All And Text, Then store in local
        imagettftext($background, 18, 0, 280, 100, $fontcolor, $fonttype, $vip['nickname']);
        imagejpeg($background, './QRcode/promotion/' . "employee" . $vip['openid'] . '.jpg');

        // 生成二维码推广图片 结束==================

        // 上传下载相应
        $file = getcwd() . "/QRcode/promotion/" . "employee" . $vip['openid'] . '.jpg';
        if (file_exists($file)) {
            $mediaId = WechatHelper::uploadMedia($file);
            WechatHelper::responseCustomerServiceImage($openid, $mediaId);
        } else {
            $msg = "员工二维码生成失败";
            WechatHelper::responseCustomerServiceText($openid, $msg);
        }
        // 上传下载相应 结束

        // 过滤连续请求-关闭
        F("employee" . $vip['openid'], null);

        // 后续数据操作（写入头像到本地，更新个人信息）
        if ($mark) {
            $tempvip = self::$_wx->getUserInfo($openid);
            $vip['nickname'] = $tempvip['nickname'];
            $vip['headimgurl'] = $tempvip['headimgurl'];
            $vipModel->save($vip);
        } else {
            // 将头像文件写入
            imagejpeg($headimg, './QRcode/headimg/' . $vip['openid'] . '.jpg');
        }
    }


    public function reply4TuiGuangErWeiMa($openid)
    {
        // 获取用户信息
        $map['openid'] = $openid;

        $vipModel = M('Vip');
        $vip = $vipModel->where($map)->find();

        CommonLoger::log("aaa", "22");
        // 用户校正
        if (!$vip) {
            $msg = "用户信息缺失，请重新关注公众号";
            //self::$_wx->text($msg)->reply();
            WechatHelper::responseCustomerServiceText($openid, $msg);
            exit();
        } else if ($vip['isfx'] == 0) {
            $shopSet = M('Shop_set')->find();
            $msg = "您还未成为" . $shopSet['fxname'] . "，请先购买成为" . $shopSet['fxname'] . "！";
            //self::$_wx->text($msg)->reply();
            WechatHelper::responseCustomerServiceText($openid, $msg);
            exit();
        }

        CommonLoger::log("aaa", "33");

        // 过滤连续请求-打开
        if (F($vip['openid']) != null) {
            CommonLoger::log("aaa", "331");
            $msg = "推广二维码正在生成，请稍等！";
            //self::$_wx->text($msg)->reply();
            WechatHelper::responseCustomerServiceText($openid, $msg);
            CommonLoger::log("aaa", "332");
            exit();
        } else {
            CommonLoger::log("aaa", "333");
            F($vip['openid'], $vip['openid']);
        }

        CommonLoger::log("aaa", "44");

        // 生产二维码基本信息，存入本地文档，获取背景
        $background = WxBiz::createQrcodeBg4Common(); //$this->createQrcodeBg();
        //WechatHelper::responseCustomerServiceText($openid,$background);
        $qrcode = WxBiz::createQrcode4Common($vip['id'], $vip['openid']);
        if (!$qrcode) {
            $msg = "专属二维码 生成失败";
            //self::$_wx->text($msg)->reply();
            WechatHelper::responseCustomerServiceText($openid, $msg);
            F($vip['openid'], null);
            exit();
        }

        CommonLoger::log("aaa", "55");
        // 生产二维码基本信息，存入本地文档，获取背景 结束

        // 获取头像信息
        $mark = false; // 是否需要写入将图片写入文件

        //WechatHelper::responseCustomerServiceText($openid,$vip['headimgurl']);
        $imageUrl = $vip['headimgurl'];
        $wxAvatarIp = C('WX_AVATARSERVER_IP');
        if ($wxAvatarIp) {
            $imageUrl = str_replace('wx.qlogo.cn', $wxAvatarIp, $imageUrl);
        }
        $headimg = NetHelper::request($imageUrl);
        //WechatHelper::responseCustomerServiceText($openid,$headimg);
        if (!$headimg) {// 没有头像先从头像库查找，再没有就选择默认头像
            if (file_exists('./QRcode/headimg/' . $vip['openid'] . '.jpg')) { // 获取不到远程头像，但存在本地头像，需要更新
                $headimg = file_get_contents('./QRcode/headimg/' . $vip['openid'] . '.jpg');
            } else {
                $headimg = file_get_contents('./QRcode/headimg/' . 'default' . '.jpg');
            }
            $mark = true;
        }

        $headimg = imagecreatefromstring($headimg);

//        $recommenduseravatar = $vip['headimgurl'];
//        if (empty($recommenduseravatar)) {
//            if (file_exists('./QRcode/headimg/' . $vip['openid'] . '.jpg')) { // 获取不到远程头像，但存在本地头像，需要更新
//                $recommenduseravatar = PHYSICAL_ROOT_PATH.'/QRcode/headimg/' . $vip['openid'] . '.jpg';
//            } else {
//                $recommenduseravatar = PHYSICAL_ROOT_PATH.'/QRcode/headimg/' . 'default' . '.jpg';
//            }
//
//            $recommenduseravatar = str_replace('/', '\\', $recommenduseravatar);
//            $mark = true;
//        }
//
//        CommonLoger::log("aaa", "661");
//        $headimg = ImageHelper::loadImage($recommenduseravatar, 'non');

        CommonLoger::log("aaa", "662");
        //$headimg = imagecreatefromstring($headimg);
        // 获取头像信息 结束

        // 生成二维码推广图片=======================

        // Combine QRcode and background and HeadImg
        $b_width = imagesx($background);
        $b_height = imagesy($background);
        $q_width = imagesx($qrcode);
        $q_height = imagesy($qrcode);
        $h_width = imagesx($headimg);
        $h_height = imagesy($headimg);
        imagecopyresampled($background, $qrcode, $b_width * 0.24, $b_height * 0.5, 0, 0, 297, 297, $q_width, $q_height);
        imagecopyresampled($background, $headimg, $b_width * 0.10, 12, 0, 0, 120, 120, $h_width, $h_height);

        // Set Font Type And Color
        $fonttype = './Public/Common/fonts/wqy-microhei.ttc';
        $fontcolor = imagecolorallocate($background, 0x00, 0x00, 0x00);

        // Combine All And Text, Then store in local
        imagettftext($background, 18, 0, 280, 100, $fontcolor, $fonttype, $vip['nickname']);
        imagejpeg($background, './QRcode/promotion/' . $vip['openid'] . '.jpg');

        CommonLoger::log("aaa", "77");
        // 生成二维码推广图片 结束==================

        //WechatHelper::responseCustomerServiceText($openid,'dddddddddddddddd');
        // 上传下载相应
        $file = getcwd() . "/QRcode/promotion/" . $vip['openid'] . '.jpg';
        if (file_exists($file)) {
            CommonLoger::log('file', $file);
            $mediaId = WechatHelper::uploadMedia($file);
            WechatHelper::responseCustomerServiceImage($openid, $mediaId);
        } else {
            $msg = "专属二维码生成失败";
            //self::$_wx->text($msg)->reply();
            WechatHelper::responseCustomerServiceText($openid, $msg);
        }
        // 上传下载相应 结束

        CommonLoger::log("aaa", "88");
        // 过滤连续请求-关闭
        F($vip['openid'], null);

        // 后续数据操作（写入头像到本地，更新个人信息）
        if ($mark) {
            $tempvip = self::$_wx->getUserInfo($openid); //$this->apiClient(self::$_revdata['FromUserName']);
            $vip['nickname'] = $tempvip['nickname'];
            $vip['headimgurl'] = $tempvip['headimgurl'];
            $vipModel->save($vip);
        } else {
            // 将头像文件写入
            imagejpeg($headimg, './QRcode/headimg/' . $vip['openid'] . '.jpg');
        }
    }
}