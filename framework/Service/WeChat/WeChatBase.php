<?php

namespace Framework\Service\WeChat;

use Framework\Facade\App;
use Framework\Facade\Log;
use Framework\Facade\Image;
use Framework\Facade\Config;

/**
 * 微信基础功能
 */
class WeChatBase {

    use WeChatConnect,
        WeChatToken;

    /**
     * 微信AccessToken
     * @param string $strGzhKey 公众号key
     * @param boolean $blnForce 强制更新token
     * @return string
     */
    private function getAccessToken($strGzhKey, $blnForce = false) {
        $arrAccessToken = $this->getAccessTokenCache($strGzhKey);
        $strAccessToken = '';
        //如果还没有Token信息或超过过期时间则需要获取
        if ($blnForce || ($arrAccessToken['accesstoken'] == '' || $arrAccessToken['expiretime'] < time())) {
            $strAppId = Config::get("wechat.gzh.{$strGzhKey}.appid");
            $strAppSecret = Config::get("wechat.gzh.{$strGzhKey}.appsecret");

            $strUrl = Config::get("wechat.api.access_token");
            $strUrl = str_replace('{APPID}', $strAppId, $strUrl);
            $strUrl = str_replace('{APPSECRET}', $strAppSecret, $strUrl);

            $mixRes = $this->httpExec($strUrl);
            if ($mixRes === false) {
                $strAccessToken = '';
            } else {
                $mixRes = json_decode($mixRes, true);
                $strAccessToken = $mixRes['access_token'];
                if (!empty($strAccessToken)) {
                    $this->setAccessTokenCache($strGzhKey, $strAccessToken);
                }
            }
        } else {
            $strAccessToken = $arrAccessToken['accesstoken'];
        }
        return $strAccessToken;
    }

    /**
     * 微信JsapiTicket
     * @param string $strGzhKey 公众号key
     * @param boolean $blnForce 强制更新token
     * @return string
     */
    private function getJsapiTicket($strGzhKey, $blnForce = false) {
        $arrJsapiTicket = $this->getJsapiTicketCache($strGzhKey);
        $strJsapiTicket = '';
        //如果还没有JsapiTicket信息或超过过期时间则需要获取
        if ($blnForce || ($arrJsapiTicket['jsapiticket'] == '' || $arrJsapiTicket['expiretime'] < time())) {
            $strUrl = Config::get("wechat.api.jsapi_ticket");
            $strUrl = str_replace('{ACCESS_TOKEN}', $this->getAccessToken($strGzhKey), $strUrl);

            $mixRes = $this->httpExec($strUrl);
            if ($mixRes === false) {
                $strJsapiTicket = '';
            } else {
                $mixRes = json_decode($mixRes, true);
                $strJsapiTicket = $mixRes['ticket'];
                if (!empty($strJsapiTicket)) {
                    $this->setJsapiTicketCache($strGzhKey, $strJsapiTicket, $arrJsapiTicket['jsapiticket']);
                }
            }
        } else {
            $strJsapiTicket = $arrJsapiTicket['jsapiticket'];
        }
        return $strJsapiTicket;
    }

    /**
     * 检查是否为token错误
     * @param mix $mixRes
     * @return boolean
     */
    private function checkTokenError($mixRes) {
        if (in_array($mixRes['errcode'], ['40001', '40014', '42001'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 发送模板消息(curl)
     * 等待微信返回结果
     * @param string $strGzhKey 公众号key
     * @param string $strMsg 消息
     * @param bool $blnTry 当前是否为重试执行
     * @return mix
     */
    protected function sendTemplateMsgCurl($strGzhKey, $strMsg, $blnTry = false) {
        $strUrl = Config::get("wechat.api.template_msg");
        $strUrl = str_replace('{ACCESS_TOKEN}', $this->getAccessToken($strGzhKey), $strUrl);
        $mixRes = $this->httpExec($strUrl, $strMsg);
        $mixRes = $mixRes === false ? [ 'errmsg' => 'curl_err', 'errcode' => 'curl_err'] : json_decode($mixRes, true);

        //token异常重新发送
        if ($this->checkTokenError($mixRes) && $blnTry === false) {
            //记录发送过程的token错误
            $strLog = sprintf("\n gzhkey:%s \n msg:%s \n error:%s \n", $strGzhKey, $strMsg, json_encode($mixRes));
            Log::log($strLog, Config::get('const.Log.LOG_WECHATTOKENINFO'));

            //强制更新token
            $this->getAccessToken($strGzhKey, true);

            //消息重发
            return $this->sendTemplateMsgCurl($strGzhKey, $strMsg, true);
        }
        return $mixRes;
    }

    /**
     * 发送模板消息(socket)
     * 不等待微信返回结果
     * @param string $strGzhKey 公众号key
     * @param string $strMsg 消息
     * @return mix
     */
    protected function sendTemplateMsgSocket($strGzhKey, $strMsg) {
        $objFsp = fsockopen('api.weixin.qq.com', 80, $intErrNo, $strErrStr, 3);
        $arrRes = [];
        if (!$objFsp) {
            $arrRes = ['errmsg' => $strErrStr, 'errcode' => 'socket_open_err'];
        } else {
            $strAccessToken = $this->getAccessToken($strGzhKey);
            $strPost = "POST /cgi-bin/message/template/send?access_token={$strAccessToken} HTTP/1.1\r\n";
            $strPost.="Host:api.weixin.qq.com\r\n";
            $strPost.="Content-type:application/x-www-form-urlencoded\r\n";
            $strPost.="Content-Length:" . strlen($strMsg) . "\r\n";
            $strPost.="Connection:close\r\n";
            $strPost.="\r\n";
            $strPost.="{$strMsg}\r\n";
            $strPost.="\r\n";
            $blnWrite = fwrite($objFsp, $strPost);
            fclose($objFsp);
            if ($blnWrite) {
                $arrRes = [ 'errmsg' => 'ok', 'errcode' => '0'];
            } else {
                $arrRes = [ 'errmsg' => 'socket_write_err', 'errcode' => 'socket_write_err'];
            }
        }
        return $arrRes;
    }

    /**
     * 小程序登录凭证校验
     * @param string $strGzhKey 小程序key
     * @param string $strCode wx.login的code
     * @return mix
     */
    public function code2Session($strGzhKey, $strCode) {
        $strUrl = Config::get("wechat.api.code2Session");

        $strAppId = Config::get("wechat.gzh.{$strGzhKey}.appid");
        $strAppSecret = Config::get("wechat.gzh.{$strGzhKey}.appsecret");

        $strUrl = str_replace('{APPID}', $strAppId, $strUrl);
        $strUrl = str_replace('{APPSECRET}', $strAppSecret, $strUrl);
        $strUrl = str_replace('{JSCODE}', $strCode, $strUrl);

        $mixRes = $this->httpExec($strUrl);
        $mixRes = $mixRes === false ? [ 'errmsg' => 'curl_err', 'errcode' => 'curl_err'] : json_decode($mixRes, true);
        return $mixRes;
    }

    /**
     * 获取小程序二维码(无限制)
     * <br>需要小程序有已发布的版本
     * @param string $strGzhKey 小程序key
     * @param string $strPage 发布的小程序存在的页面
     * @param string $strParam 页面参数，最大为32个字符
     * @param int $intWidth 二维码宽度
     * @param bool $blnTry 当前是否为重试执行
     */
    public function getWXACodeUnlimit($strGzhKey, $strPage, $strParam, $intWidth = 280, $blnTry = false) {
        $strUrl = Config::get("wechat.api.getWXACodeUnlimit");
        $strAccessToken = $this->getAccessToken($strGzhKey);

        $strUrl = str_replace('{ACCESS_TOKEN}', $strAccessToken, $strUrl);
        $mixPost = [
            'scene' => $strParam,
            'page' => $strPage,
            'width' => $intWidth,
            'auto_color' => true,
            'line_color' => ['r' => 0, 'g' => 0, 'b' => 0],
            'is_hyaline' => true
        ];
        //需要转成json格式提交
        $mixPost = json_encode($mixPost);
        $mixRes = $this->httpExec($strUrl, $mixPost);
        $mixRes = $mixRes === false ? [ 'errmsg' => 'curl_err', 'errcode' => 'curl_err'] : (is_array(json_decode($mixRes, true)) ? json_decode($mixRes, true) : [ 'errmsg' => 'ok', 'errcode' => '0', 'qrcode' => $this->getBase64($mixRes)]);

        //token异常重新获取
        if ($this->checkTokenError($mixRes) && $blnTry === false) {
            //记录发送过程的token错误
            $strLog = sprintf("\n gzhkey:%s \n msg:%s \n error:%s \n", $strGzhKey, $mixRes['errmsg'], json_encode($mixRes));
            Log::log($strLog, Config::get('const.Log.LOG_WECHATTOKENINFO'));

            //强制更新token
            $this->getAccessToken($strGzhKey, true);

            //重新获取
            return $this->getWXACodeUnlimit($strGzhKey, $strPage, $strParam, $intWidth, true);
        }

        return $mixRes;
    }

    /**
     * 将二进制图片转成base64编码
     */
    protected function getBase64($strQrCode) {
        //生成图片
        $strTmpFile = App::make('path.storage') . '/cache/image/' . getGUID() . '.png';
        $objFile = fopen($strTmpFile, 'w');
        fwrite($objFile, $strQrCode);
        fclose($objFile);

        //图片压缩
        $strTmpFile = Image::compressImage($strTmpFile, 0.4);

        //获取base64
        $strQrCode = Image::getImageBase64($strTmpFile)['encode'];

        //删除图片
        unlink($strTmpFile);

        //返回结果
        return $strQrCode;
    }

}
