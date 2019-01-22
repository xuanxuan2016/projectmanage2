<?php

namespace Framework\Service\MessageQueue;

use Exception;
use Framework\Facade\Log;
use Framework\Facade\Config;
use Framework\Service\Lib\PhpAmqpLib\Message\AMQPMessage;

/**
 * Queue消费者基类
 */
class QueueConsumerBase extends QueueBase {

    /**
     * 初始化参数
     */
    private $arrInitParam = [];

    /**
     * 获取子类类型
     */
    protected function getType() {
        return 'consumer';
    }

    /**
     * 构建客户端
     * @param array $arrInitParam 配置信息
     * @param string $strErrorMsg (&)init的错误信息
     * @return boolean true：成功 false：失败
     */
    protected function build($arrInitParam) {
        try {
            $this->arrInitParam = $arrInitParam;
            //创建交换器与队列
            $this->createExchangeQueue($arrInitParam);
            //每次只接受一条信息
            $this->getChannel()->basic_qos(null, 1, null);
            //开始监听
            $strQueueListen = Config::get('messagequeue.system_name') . '.' . $arrInitParam['queue_listen'];
            $this->getChannel()->basic_consume($strQueueListen, '', false, false, false, false, function(AMQPMessage $objMessage) {
                $this->dealMessage($objMessage);
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
     * 从服务器接收消息，进行业务处理
     * 必须要返回true or false
     * @return boolean true：处理成功 false：处理失败
     */
    protected function receiveMessage($strMessage) {
        return false;
    }

    /**
     * 信息处理
     */
    private function dealMessage(AMQPMessage $objMessage) {
        //消息消费失败是否重进队列
        $blnIsRequeue = isset($this->arrInitParam['is_requeue']) ? $this->arrInitParam['is_requeue'] : false;
        //业务确认是否成功
        $blnAck = $this->receiveMessage($objMessage->body);
        if ($blnAck) {
            $objMessage->delivery_info['channel']->basic_ack($objMessage->delivery_info['delivery_tag']);
        } else {
            //$objMessage->delivery_info['channel']->basic_nack($objMessage->delivery_info['delivery_tag'], false, $blnReQueue);
            $objMessage->delivery_info['channel']->basic_reject($objMessage->delivery_info['delivery_tag'], $blnIsRequeue);
        }
    }

    /**
     * 开始运行
     */
    public function run() {
        while (1) {
            try {
                while (1) {
                    $this->getChannel()->wait();
                }
            } catch (Exception $e) {
                //设置错误信息
                $strErrorMsg = $e->getMessage();
                $strErrorMsg = sprintf("type:%s\r\n param:%s\r\n error:%s\r\n", $this->getType() . '_run', json_encode($this->arrInitParam), $strErrorMsg);
                //错误日志记录
                Log::log($strErrorMsg, Config::get('const.Log.LOG_MQERR'));
                //重建
                $this->reset();
                if (!$this->build($this->arrInitParam)) {
                    //日志记录
                    break;
                }
            }
        }
    }

}
