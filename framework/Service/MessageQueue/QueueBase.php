<?php

namespace Framework\Service\MessageQueue;

use Exception;
use Framework\Facade\Config;
use Framework\Service\Lib\PhpAmqpLib\Wire;
use Framework\Service\Lib\PhpAmqpLib\Wire\AMQPTable;
use Framework\Service\Lib\PhpAmqpLib\Channel\AMQPChannel;
use Framework\Service\Lib\PhpAmqpLib\Message\AMQPMessage;
use Framework\Service\Lib\PhpAmqpLib\Connection\AMQPStreamConnection;
use Framework\Service\Lib\PhpAmqpLib\Connection\AMQPSocketConnection;

/**
 * Queue基类
 */
class QueueBase {

    /**
     * rabbit连接超时时间(秒)
     */
    private $intConnectTimeOut = 3;

    /**
     * rabbit读取超时时间(秒)
     */
    private $intReadWriteTimeOut = 30;

    /**
     * 心跳时间
     */
    private $intHeartbeat = 10;

    /**
     * 连接对象
     */
    private $objConnection = null;

    /**
     * 信道对象
     */
    private $objChannel = null;

    /**
     * 获取子类类型
     */
    protected function getType() {
        return '';
    }

    /**
     * 获取连接对象
     */
    protected function getConnection() {
        if (is_null($this->objConnection)) {
            $this->createConnect();
        }
        return $this->objConnection;
    }

    /**
     * 获取信道对象
     * @param int $intChannelID 信道id
     */
    protected function getChannel($intChannelID = null) {
        if (is_null($this->objChannel) && !is_null($this->getConnection())) {
            $this->objChannel = $this->getConnection()->channel($intChannelID);
        }
        return $this->objChannel;
    }

    /**
     * 获取rabbit服务器
     */
    protected function getRabbitServer() {
        return Config::get('messagequeue.server');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        $this->reset();
    }

    /**
     * 重置连接
     * 1.置为null是不能直接关闭连接的 ,如果服务器异常会自动关闭连接
     * 2.当调用close时，如果服务器异常，也会有异常信息抛出，需要进行捕捉
     */
    protected function reset() {
        try {
            if ($this->objChannel instanceof AMQPChannel) {
                $this->objChannel->close();
            }
        } catch (Exception $e) {
            
        } finally {
            $this->objChannel = null;
        }

        try {
            if ($this->objConnection instanceof AMQPStreamConnection) {
                $this->objConnection->close();
            }
        } catch (Exception $e) {
            
        } finally {
            $this->objConnection = null;
        }
    }

    /**
     * 创建连接
     * <br>随机获取一个有效的服务器连接
     */
    private function createConnect() {
        //1.随机获取rabbit服务器
        $intTryCount = 1;
        $intTotalCount = count($this->getRabbitServer());
        $arrRabbitServer = $this->getRabbitServer();
        $arrConnErr = [];
        while ($intTryCount <= $intTotalCount) {
            $strRandomKey = rand(0, count($arrRabbitServer) - 1);
            $strRandomKey = array_keys($arrRabbitServer)[$strRandomKey];
            $arrServer = $arrRabbitServer[$strRandomKey];
            try {
                if ($this->getType() == 'producer') {
                    $this->objConnection = new AMQPSocketConnection($arrServer['host'], $arrServer['port'], $arrServer['user'], $arrServer['password'], '/', false, 'AMQPLAIN', null, 'en_US', $this->intReadWriteTimeOut, false, $this->intReadWriteTimeOut, $this->intHeartbeat);
                } else {
                    $this->objConnection = new AMQPStreamConnection($arrServer['host'], $arrServer['port'], $arrServer['user'], $arrServer['password'], '/', false, 'AMQPLAIN', null, 'en_US', $this->intConnectTimeOut, $this->intReadWriteTimeOut, null, false, $this->intHeartbeat);
                }
                break;
            } catch (Exception $e) {
                $arrConnErr[] = array_merge($arrServer, ['err' => $e->getMessage()]);
                unset($arrRabbitServer[$strRandomKey]);
            }
            $intTryCount++;
        }
        //2.检查是否能连接到服务器
        if (is_null($this->objConnection)) {
            throw new Exception('RabbitMQ服务器连接失败。' . json_encode($arrConnErr));
        }
    }

    /**
     * 创建交换器与队列并进行绑定
     * @param array $arrInitParam 配置信息
     * <br>exchange_name：交换器名，必填
     * <br>exchange_type：交换器类别，(direct,topic,fanout)，必填
     * <br>ae_exchange：备用交换器，(0,1)，默认0
     * <br>queue_bind：队列绑定，可填，[
     * <br>&nbsp;&nbsp;&nbsp;&nbsp;queue_name：队列名，必填
     * <br>&nbsp;&nbsp;&nbsp;&nbsp;route_key：路由键，可填，[key1,key2，..]
     * <br>&nbsp;&nbsp;&nbsp;&nbsp;dead_letter：死信队列，(0,1,2)，默认0
     * <br>]
     */
    protected function createExchangeQueue($arrInitParam) {
        //交换器
        if (!empty($arrInitParam['exchange_name'])) {
            $strExchangeName = Config::get('messagequeue.system_name') . '.' . $arrInitParam['exchange_name'];
            $this->declareExchange($strExchangeName, $arrInitParam['exchange_type'], isset($arrInitParam['ae_exchange']) ? $arrInitParam['ae_exchange'] : 0);
        }
        //队列
        if (isset($arrInitParam['queue_bind']) && count($arrInitParam['queue_bind']) > 0) {
            foreach ($arrInitParam['queue_bind'] as $arrQueue) {
                if (!empty($arrQueue['queue_name'])) {
                    $strQueueName = Config::get('messagequeue.system_name') . '.' . $arrQueue['queue_name'];
                    //定义队列
                    $this->declareQueue($strQueueName, $strExchangeName, isset($arrQueue['dead_letter']) ? $arrQueue['dead_letter'] : 0);
                    //绑定
                    $this->bindExchangeQueue($strExchangeName, $strQueueName, isset($arrQueue['route_key']) ? $arrQueue['route_key'] : []);
                }
            }
        }
    }

    /**
     * 创建或获取交换器
     * @param string $strExchangeName 交换器名称
     * @param string $strExchangeType 交换器类别，【'direct', 'fanout', 'topic'】
     * @param int $intAeExchange 是否需要备用交换器，默认为0
     * <br>1：当消息不能被路由时进入此交换器，自动创建备用交换器与队列并绑定
     * <br>备用交换器名：{$strExchangeName}_ae 交换器类别：fanout 队列名：{$strExchangeName}_ae
     */
    protected function declareExchange($strExchangeName, $strExchangeType, $intAeExchange = 0) {
        //1.参数校验
        if (empty($strExchangeName)) {
            throw new Exception('交换器名不能为空');
        }
        if (!in_array($strExchangeType, ['direct', 'fanout', 'topic'])) {
            throw new Exception('交换器类别错误');
        }
        //2.备用交换器
        $arrArgument = [];
        if ($intAeExchange == 1) {
            $strAeExchangeName = $strExchangeName . '_ae_exchange';
            $strAeQueueName = $strExchangeName . '_ae_queue';
            //生成参数
            $arrArgument = new AMQPTable([
                'alternate-exchange' => $strAeExchangeName
            ]);
            //1.创建备用交换器
            $this->declareExchange($strAeExchangeName, 'fanout');
            //2.创建备用队列
            $this->declareQueue($strAeQueueName);
            //3.绑定备用交换器与队列
            $this->bindExchangeQueue($strAeExchangeName, $strAeQueueName);
        }
        //3.创建交换器
        $this->getChannel()->exchange_declare($strExchangeName, $strExchangeType, false, true, false, false, false, $arrArgument);
    }

    /**
     * 创建或获取队列
     * @param string $strQueueName 队列名
     * @param string $strExchangeName 交换器名
     * @param int $intDeadLetter 死信队列，默认为0
     * <br>0:不需要
     * <br>当消息被拒绝,过期,队列达到最大长度 ，会进入死信队列；
     * <br>1:自动创建死信交换器与队列并绑定
     * <br>2:使用原交换器，消息回到原队列
     */
    protected function declareQueue($strQueueName, $strExchangeName = '', $intDeadLetter = 0) {
        //1.参数校验
        if (empty($strQueueName)) {
            throw new Exception('队列名不能为空');
        }
        //2.死信队列
        //可设置配置为原先的交换器，不设置routekey，实现消息循环
        $arrArgument = [];
        switch ($intDeadLetter) {
            case 1:
                $strDqExchangeName = $strQueueName . "_dq_exchange";
                $strDqQueueName = $strQueueName . "_dq_queue";
                $strDqRouteKey = "dq_rk";
                //生成参数
                $arrArgument = new AMQPTable([
                    'x-dead-letter-exchange' => $strDqExchangeName,
                    'x-dead-letter-routing-key' => $strDqRouteKey
                ]);
                //1.创建死信交换器
                $this->declareExchange($strDqExchangeName, 'direct');
                //2.创建死信队列
                $this->declareQueue($strDqQueueName);
                //3.绑定死信交换器与队列
                $this->bindExchangeQueue($strDqExchangeName, $strDqQueueName, [$strDqRouteKey]);
                break;
            case 2:
                //生成参数
                $arrArgument = new AMQPTable([
                    'x-dead-letter-exchange' => $strExchangeName
                ]);
                break;
        }
        //3.创建队列
        $this->getChannel()->queue_declare($strQueueName, false, true, false, false, false, $arrArgument);
    }

    /**
     * 交换器与队列绑定
     * @param string $strExchangeName 交换器名
     * @param string $strQueueName 队列名
     * @param array $arrRoutingKey 路由键
     */
    protected function bindExchangeQueue($strExchangeName, $strQueueName, $arrRoutingKey = []) {
        //1.参数校验
        if (empty($strExchangeName) || empty($strQueueName)) {
            throw new Exception('交换器名与队列名不能为空');
        }
        //2.绑定
        if (count($arrRoutingKey) == 0) {
            $this->getChannel()->queue_bind($strQueueName, $strExchangeName);
        } else {
            foreach ($arrRoutingKey as $strRoutingKey) {
                $this->getChannel()->queue_bind($strQueueName, $strExchangeName, $strRoutingKey);
            }
        }
    }

}
