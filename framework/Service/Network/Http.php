<?php

namespace Framework\Service\Network;

use Exception;
use Framework\Facade\Log;
use Framework\Facade\Config;

class Http {

    /**
     * 执行http请求
     * @param string $strUrl url
     * @param mix $mixPost post数据
     * @param int $intTimeout 访问超时时间
     * @param array $arrHttpHeader 请求头
     * @param string $strProxy 代理
     * @return boolean|object
     */
    public function curl($strUrl, $mixPost = null, $intTimeout = 10, $arrHttpHeader = [], $strProxy = '') {
        $objCurl = curl_init();
        curl_setopt($objCurl, CURLOPT_URL, $strUrl);
        curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($objCurl, CURLOPT_TIMEOUT, $intTimeout);

        //如果需要post参数的话
        if (isset($mixPost)) {
            curl_setopt($objCurl, CURLOPT_POST, true);
            curl_setopt($objCurl, CURLOPT_POSTFIELDS, $mixPost);
        }

        //设置请求头
        if (!empty($arrHttpHeader)) {
            curl_setopt($objCurl, CURLOPT_HTTPHEADER, $arrHttpHeader);
        }

        //设置代理
        if (!empty($strProxy)) {
            curl_setopt($objCurl, CURLOPT_PROXY, $strProxy);
        }

        //执行与返回结果
        $mixRes = curl_exec($objCurl);
        $intErrNo = curl_errno($objCurl);
        if ($intErrNo > 0) {
            curl_close($objCurl);
            $strLog = sprintf("\n url:%s \n post:%s \n errno:%s \n err:%s \n", $strUrl, (is_null($mixPost) ? '' : (is_array($mixPost) ? json_encode($mixPost) : $mixPost)), $intErrNo, curl_error($objCurl));
            Log::log($strLog, Config::get('const.Log.LOG_CURLEERR'));
            return false;
        } else {
            $intHttpCode = curl_getinfo($objCurl, CURLINFO_HTTP_CODE);
            if ($intHttpCode != 200) {
                curl_close($objCurl);
                $strLog = sprintf("\n url:%s \n post:%s \n httpcode:%s \n", $strUrl, (is_null($mixPost) ? '' : (is_array($mixPost) ? json_encode($mixPost) : $mixPost)), $intHttpCode);
                Log::log($strLog, Config::get('const.Log.LOG_CURLEERR'));
                return false;
            } else {
                curl_close($objCurl);
                return $mixRes;
            }
        }
    }

    /**
     * 从本地或远程读取文件
     * @param string $strFilePath 文件地址
     * @param int $intTimeout 超时时间
     * @param int $intTryCount 重试次数
     * @return boolean|string
     */
    public function fileGetContents($strFilePath, $intTimeout = 10, $intTryCount = 1) {
        //创建上下文对象
        $arrOption = [
            'http' => ['timeout' => $intTimeout]
        ];
        $objContext = stream_context_create($arrOption);

        //读取文件
        $intTryCountTmp = 0;
        $intTryCount = $intTryCount + 1;
        while ($intTryCountTmp < $intTryCount && ($mixRes = file_get_contents($strFilePath, false, $objContext)) === false) {
            $intTryCountTmp++;
        }
        return $mixRes;
    }

    /**
     * 执行socket请求
     * <br>1.这里有个unpack的处理，如果需要通用需要修改
     * @param string $strHost 请求host
     * @param string $strPort 请求port
     * @param string $strMessage 发送给端口的信息
     * @param int $intReadTimeout 读取超时
     * @param int $intWriteTimeout 写入超时
     * @return boolean|string 失败返回false，成功返回socket的读取信息
     */
    public function socket($strHost, $strPort, $strMessage, $intReadTimeout = 5, $intWriteTimeout = 5, $intLenBytes = 4) {
        try {
            //1.创建socket
            $objSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($objSocket === false) {
                throw new Exception('socket创建失败');
            }
            
            //2.设置option
            socket_set_option($objSocket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => $intReadTimeout, "usec" => 0));
            socket_set_option($objSocket, SOL_SOCKET, SO_SNDTIMEO, array("sec" => $intWriteTimeout, "usec" => 0));
            
            //3.连接socket
            if (socket_connect($objSocket, $strHost, $strPort) === false) {
                throw new Exception('socket连接失败');
            }
            
            //4.发送信息
            if (socket_write($objSocket, $strMessage, strlen($strMessage)) == false) {
                throw new Exception('socket连接失败');
            }
            
            //5.1 读取本次请求数据的字节数
            $mixBinary = socket_read($objSocket, $intLenBytes);
            if ($mixBinary === false) {
                return false;
            }
            $intLen = unpack('N', mb_substr($mixBinary, 0, $intLenBytes, '8bit'))[1];
            //5.2 读取拼接具体数据
            $strRsv = '';
            while ($intLen > 0) {
                $strRsvTmp = socket_read($objSocket, 1024);
                $strRsv .= $strRsvTmp;
                $intLen -= mb_strlen($strRsvTmp, '8bit');
            }
            
            //6.返回结果
            return $strRsv;
        } catch (Exception $e) {
            //记录错误信息
            $strErrCode = '';
            $strErrMsg = '';
            if ($objSocket) {
                $strErrCode = socket_last_error($objSocket);
                $strErrMsg = socket_strerror($strErrCode);
            }
            $strLog = sprintf("\n host:%s \n port:%s \n message:%s \n errcode:%s \n errmsg:%s \n exception:%s \n", $strHost, $strPort, $strMessage, $strErrCode, $strErrMsg, $e->getMessage());
            Log::log($strLog, Config::get('const.Log.LOG_CURLEERR'));
            //返回
            return false;
        } finally {
            //关闭socket
            if ($objSocket) {
                socket_close($objSocket);
            }
        }
    }

}
