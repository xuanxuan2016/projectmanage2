<?php

/**
 * web配置
 */
return [
    /**
     * 默认的重定向配置
     * 可在程序中按需修改
     */
    'redirect' => [
        'uri_empty' => 'http://dev2.beautymyth.cn/web/missing/pagemiss',
        'uri_wrong' => 'http://dev2.beautymyth.cn/web/missing/pagemiss',
        'auth_wrong' => 'http://dev2.beautymyth.cn/web/common/login',
        'controller_wrong' => 'http://dev2.beautymyth.cn/web/missing/pagemiss',
        'has_login' => 'http://dev2.beautymyth.cn/web/common/home'
    ],
    /**
     * 根域名
     */
    'domain' => [
        'pc' => 'http://dev2.beautymyth.cn/'
    ],
    /**
     * js配置
     */
    'js' => [
        /**
         * 域名
         */
        'domain' => 'http://dev2.beautymyth.cn/js/',
        /**
         * 版本号
         */
        'version' => '1',
        /**
         * 是否直接读取压缩的文件
         * 0:开发环境
         * 1:测试与线上环境
         */
        'read_only' => 0
    ],
    /**
     * css配置
     */
    'css' => [
        /**
         * 域名
         */
        'domain' => 'http://dev2.beautymyth.cn/css/',
        /**
         * 版本号
         */
        'version' => '1',
        /**
         * 是否直接读取压缩的文件
         * 0:开发环境
         * 1:测试与线上环境
         */
        'read_only' => 0
    ],
    /**
     * image地址
     */
    'image' => [
        /**
         * 域名
         */
        'domain' => 'http://dev2.beautymyth.cn/images/'
    ]
];
