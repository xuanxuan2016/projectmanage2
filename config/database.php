<?php

/**
 * 数据库配置
 */
return [
    /**
     * 默认业务类型
     * 当通过表确定不了业务类型时
     */
    'default_business_type' => 'projectmanage',
    /**
     * 是否记录info
     */
    'log_info' => true,
    /**
     * 业务信息
     */
    'business_info' => [
        /**
         * 业务类别
         */
        'projectmanage' => [
            /**
             * 主库是否参与读
             */
            'master_read' => 1,
            /**
             * 连接超时时间
             */
            'connect_timeout' => 3,
            /**
             * 长连接
             */
            'persistent' => false,
            /**
             * 主库
             */
            'master' => [
                'business' => 'projectmanage',
                'type' => 'mysql',
                'host' => '10.100.3.106',
                'port' => '3306',
                'db' => 'projectmanage',
                'username' => 'hrodbadmin',
                'password' => 'U7ugbrXylVIH5hnm+0anxo0BorjjWb+0DJgl9mdzbame2EsRHfWULYr3XvZMR9Kg4434m6e98T0yAaEZrHbiXgwPKONTk8aCyzlg6p0BlnM='
            ],
            /**
             * 从库
             */
            'slave' => [
                [
                    'business' => 'projectmanage',
                    'type' => 'mysql',
                    'host' => '10.100.3.106',
                    'port' => '3306',
                    'db' => 'projectmanage',
                    'username' => 'hrodbadmin',
                    'password' => 'U7ugbrXylVIH5hnm+0anxo0BorjjWb+0DJgl9mdzbame2EsRHfWULYr3XvZMR9Kg4434m6e98T0yAaEZrHbiXgwPKONTk8aCyzlg6p0BlnM='
                ]
            ],
            /**
             * 连接池信息
             */
            'connect_pool' => [
            ],
        ]
    ],
    /**
     * 表信息
     */
    'table_info' => [
    ]
];

