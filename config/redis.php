<?php

/**
 * 缓存配置
 */
return [
    /**
     * 主服务器优先配置在前面
     */
    'server' => [
        ['host' => '10.100.3.106', 'port' => '6379'],
        ['host' => '10.100.3.106', 'port' => '6379']
    ],
    /**
     * 连接超时时间
     */
    'connect_timeout' => 3,
    /**
     * 读取超时时间
     */
    'read_timeout' => 3,
    /**
     * 持久连接id
     */
    'persistent_id' => 'projectmanage',
    /**
     * 是否记录info
     */
    'log_info' => true
];

