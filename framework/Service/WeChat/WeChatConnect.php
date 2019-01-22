<?php

namespace Framework\Service\WeChat;

use Framework\Facade\Log;
use Framework\Facade\Config;

/**
 * 微信连接
 */
trait WeChatConnect {

    /**
     * 通过cUrl模拟http访问
     * @param string $strUrl Url
     * @param mix $mixJsonPost Json数据
     */
    public function httpExec($strUrl, $mixJsonPost = null, $intTimeout = 10) {
        $objCurl = curl_init();
        curl_setopt($objCurl, CURLOPT_URL, $strUrl); //设置Url
        curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true); //获取的信息以文件流的形式返回，而不是直接输出
        curl_setopt($objCurl, CURLOPT_TIMEOUT, $intTimeout); //设置cURL允许执行的最长秒数
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($objCurl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($objCurl, CURLOPT_SSL_VERIFYHOST, 2);
        if (isset($mixJsonPost)) {//如果需要post参数的话
            curl_setopt($objCurl, CURLOPT_POST, true);
            curl_setopt($objCurl, CURLOPT_POSTFIELDS, $mixJsonPost);
        }
        $mixRes = curl_exec($objCurl);
        if ($mixRes === false) {
            $strErrLog = sprintf("\n error:%s \n url:%s \n param:%s \n ", curl_error($objCurl), $strUrl, (is_null($mixJsonPost) ? '' : (is_array($mixJsonPost) ? json_encode($mixJsonPost) : $mixJsonPost)));
            Log::log($strErrLog, Config::get('const.Log.LOG_CURLEERR'));
        }
        curl_close($objCurl);
        return $mixRes;
    }

}
