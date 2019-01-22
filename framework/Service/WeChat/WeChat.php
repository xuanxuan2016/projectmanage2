<?php

namespace Framework\Service\WeChat;

use Framework\Facade\Log;
use Framework\Facade\Config;

/**
 * 提供给外部使用的功能
 */
class WeChat extends WeChatBase {

    /**
     * 发送模板消息
     * @param string $strGzhKey 公众号key
     * @param string $strMsg 消息
     * @param string $intLevel 消息级别
     * <br>0:socket发送 1:curl发送
     * @return array
     */
    public function sendMsg($strGzhKey, $strMsg, $intLevel = 1) {
        $dateStartTime = getMicroTime();
        if ($intLevel == 0) {
            $mixRes = $this->sendTemplateMsgSocket($strGzhKey, $strMsg);
        } else {
            $mixRes = $this->sendTemplateMsgCurl($strGzhKey, $strMsg);
        }
        $dateEndTime = getMicroTime();

        $this->sendMsgLog($strGzhKey, $strMsg, $intLevel, $mixRes, $dateStartTime, $dateEndTime);

        return $mixRes;
    }

    /**
     * 记录消息日志
     */
    protected function sendMsgLog($strGzhKey, $strMsg, $intLevel, $mixRes, $dateStartTime, $dateEndTime) {
        $intLogType = Config::get('wechat.template_msg.log_type');
        switch ($intLogType) {
            case 1:
                $strLog = sprintf("\n starttime:%s \n endtime:%s \n gzhkey:%s \n msg:%s \n level:%s \n res:%s \n", $dateStartTime, $dateEndTime, $strGzhKey, $strMsg, $intLevel, json_encode($mixRes));
                Log::log($strLog, Config::get('const.Log.LOG_WECHATMSGINFO'));
                break;
            case 2:
                if ($mixRes['errmsg'] != 'ok') {
                    $strLog = sprintf("\n starttime:%s \n endtime:%s \n gzhkey:%s \n msg:%s \n level:%s \n res:%s \n", $dateStartTime, $dateEndTime, $strGzhKey, $strMsg, $intLevel, json_encode($mixRes));
                    Log::log($strLog, Config::get('const.Log.LOG_WECHATMSGINFO'));
                }
                break;
            default:
                break;
        }
    }

}
