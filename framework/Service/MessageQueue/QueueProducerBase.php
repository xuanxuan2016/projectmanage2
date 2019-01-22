<?php

namespace Framework\Service\MessageQueue;

use Exception;
use Framework\Facade\Log;
use Framework\Facade\Config;
use Framework\Service\Lib\PhpAmqpLib\Message\AMQPMessage;
use Framework\Service\Lib\PhpAmqpLib\Exception\AMQPConnectionException;

/**
 * Queue生产者基类
 */
class QueueProducerBase extends QueueBase {

    /**
     * 初始化参数
     */
    private $arrInitParam = [];

    /**
     * 此次发送消息的总数
     */
    private $intTotalMessage = 0;

    /**
     * 发送失败的消息
     */
    private $arrFailMessage = [];

    /**
     * 获取子类类型
     */
    protected function getType() {
        return 'producer';
    }

    /**
     * 构建客户端
     * @param array $arrInitParam 配置信息
     * @param boolean $blnCreate 是否创建交换器与队列，默认为false
     * <br>如果已经有了，就不需要重复创建了
     * @param string $strErrorMsg (&)init的错误信息
     * @return boolean true：成功 false：失败
     */
    public function build($arrInitParam, $blnCreate = false, &$strErrorMsg = '') {
        try {
            $this->arrInitParam = $arrInitParam;
            if ($blnCreate) {
                //创建交换器与队列
                $this->createExchangeQueue($arrInitParam);
            }
            //开启信道确认模式
            $this->getChannel()->confirm_select();
            //设置信道回调方法
            $this->getChannel()->set_ack_handler(function(AMQPMessage $objMessage) {
                $this->ackHandler($objMessage);
            });
            $this->getChannel()->set_nack_handler(function(AMQPMessage $objMessage) {
                $this->nackHandler($objMessage);
            });
            //返回true
            return true;
        } catch (Exception $e) {
            //设置错误信息
            $strErrorMsg = $e->getMessage();
            $strErrorMsg = sprintf("type:%s\r\n param:%s\r\n error:%s\r\n", $this->getType() . '_build', json_encode($arrInitParam), $strErrorMsg);
            //错误日志记录
            Log::log($strErrorMsg, Config::get('const.Log.LOG_MQERR'));
            //返回false
            return false;
        }
    }

    /**
     * 发送成功调用
     * @param AMQPMessage $objMessage
     */
    private function ackHandler(AMQPMessage $objMessage) {
        $this->intTotalMessage--;
    }

    /**
     * 发送失败调用
     * @param AMQPMessage $objMessage
     */
    private function nackHandler(AMQPMessage $objMessage) {
        $this->arrFailMessage[] = $objMessage->body;
    }

    /**
     * 消息发送
     * @param string $strExchangeName 交换器
     * @param array $arrMessage 需要发送的消息，格式[['message'=>'消息实体','route_key'=>'路由键(可为空)']]
     * @param array $arrFailMessage (&)发送失败的消息
     * @param int $intFailCount (&)发送失败的消息个数
     * @param string $strErrorMsg (&)send的错误信息
     * @return boolean true：成功 false：失败
     */
    protected function send($strExchangeName, $arrMessage, &$arrFailMessage = [], &$intFailCount = 0, &$strErrorMsg = '') {
        $intFailTryCount = Config::get('messagequeue.producer_try_count');
        $strExchangeName = Config::get('messagequeue.system_name') . '.' . $strExchangeName;

        while ($intFailTryCount >= 0) {
            try {
                $this->intTotalMessage = $intFailCount = count($arrMessage);
                $this->arrFailMessage = [];
                //批量发送
                foreach ($arrMessage as $arrMsg) {
                    $objMessage = new AMQPMessage($arrMsg['message'], ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
                    $this->getChannel()->batch_basic_publish($objMessage, $strExchangeName, isset($arrMsg['route_key']) ? $arrMsg['route_key'] : '');
                }
                $this->getChannel()->publish_batch();
                //等待
                $this->getChannel()->wait_for_pending_acks();
                //返回true or false
                $arrFailMessage = $this->intTotalMessage == $intFailCount ? $arrMessage : $this->arrFailMessage;
                $intFailCount = $this->intTotalMessage;
                return $this->intTotalMessage == 0 ? true : false;
            } catch (Exception $e) {
                if ($this->intTotalMessage == $intFailCount && $intFailTryCount > 0) {
                    //如果一条消息都没发送成功，尝试重做
                    $intFailTryCount--;
                    $this->reset();
                    if (!$this->build($this->arrInitParam)) {
                        return false;
                    }
                } else {
                    //设置错误信息
                    $strErrorMsg = $e->getMessage();
                    $strErrorMsg = sprintf("type:%s\r\n param:%s\r\n error:%s\r\n", $this->getType() . '_send', json_encode($this->arrInitParam), $strErrorMsg);
                    //错误日志记录
                    Log::log($strErrorMsg, Config::get('const.Log.LOG_MQERR'));
                    //返回false
                    return false;
                }
            }
        }
    }

}
