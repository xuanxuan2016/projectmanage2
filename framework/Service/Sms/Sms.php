<?php

namespace Framework\Service\Sms;

use Framework\Facade\Config;
use Framework\Facade\Http;

class Sms {

    /**
     * 发送短信接口
     * @param string $strMobile 手机号
     * @param string $strContent 内容
     * @param string $strSystemType 短信类别，验证码使用40，其它使用20
     */
    public function sendSMS($strMobile, $strContent, $strSystemType = '20') {
        $arrInfo['systemType'] = $strSystemType;
        $arrInfo['mobileNum'] = $strMobile;
        $arrInfo['customer'] = '';
        $arrInfo['msgType'] = '0';
        $arrInfo['content'] = $strContent;
        $arrInfo['keyNum'] = '';
        //这里需要将参数拼进url里面
        $mixRes = Http::curl(Config::get('api.ehr.sms_path') . http_build_query($arrInfo));
        if ($mixRes === false) {
            return false;
        } else {
            //解析返回的xml
            $mixRes = simplexml_load_string($mixRes, 'SimpleXMLElement', LIBXML_NOCDATA);
            //$mixRes->SendResult->ErrorMsg;
            return $mixRes->SendResult->ErrorNo == 0 ? true : false;
        }
    }

}
