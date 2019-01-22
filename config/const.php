<?php

/**
 * 类中的常量配置
 */
return [
    /**
     * 日志类
     */
    'Log' => [
        /**
         * 常规日志
         */
        'LOG_INFO' => 'INFO',
        /**
         * 错误日志
         */
        'LOG_ERR' => 'ERR',
        /**
         * sql普通日志
         */
        'LOG_SQLINFO' => 'SQLINFO',
        /**
         * sql错误
         */
        'LOG_SQLERR' => 'SQLERR',
        /**
         * redis错误
         */
        'LOG_REDISERR' => 'REDISERR',
        /**
         * redis重试
         */
        'LOG_REDISTYR' => 'REDISTRY',
        /**
         * redis普通日志
         */
        'LOG_REDISINFO' => 'REDISINFO',
        /**
         * 消息队列错误
         */
        'LOG_MQERR' => 'MESSAGEQUEUEERR',
        /**
         * curl调用错误
         */
        'LOG_CURLEERR' => 'CURLEERR',
        /**
         * 微信调用接口时token异常日志
         */
        'LOG_WECHATTOKENINFO' => 'WECHATTOKENINFO',
        /**
         * 微信模板消息日志
         */
        'LOG_WECHATMSGINFO' => 'WECHATMSGINFO',
        /**
         * 本系统接口日志
         */
        'LOG_APIINFO' => 'APIINFO',
        /**
         * 其它系统接口错误日志
         */
        'LOG_APIERR' => "APIERR",
        /**
         * 创建消息日志
         */
        'LOG_CREATEMSGINFO' => 'CREATEMSGINFO'
    ],
    /**
     * 埋点日志类
     */
    'LogEvent' => [
        /**
         * 日志类别：事件日志
         */
        'LOG_TYPE_EVENT' => 'event',
        /**
         * 日志类别：页面访问日志
         */
        'LOG_TYPE_PAGEVIEW' => 'pageView'
    ],
    /**
     * 数据格式校验类
     */
    'ValidFormat' => [
        /**
         * int
         */
        'FORMAT_INT' => 'int',
        /**
         * pos int
         * 正整数
         */
        'FORMAT_POSINT' => 'posint',
        /**
         * decimal
         */
        'FORMAT_DECIMAL' => 'decimal',
        /**
         * datetime
         */
        'FORMAT_DATETIME' => 'datetime',
        /**
         * guid
         */
        'FORMAT_UNIQ' => 'uniq',
        /**
         * email
         */
        'FORMAT_EMAIL' => 'email',
        /**
         * 身份证
         */
        'FORMAT_IDCARD' => 'idcard',
        /**
         * 手机
         */
        'FORMAT_MOBILE' => 'mobile',
        /**
         * 用户名：需要以字母开头，长度为6-18位，只能包含字母数字下划线
         */
        'FORMAT_USERNAME' => 'username',
        /**
         * 密码：需要包含字母与数字，长度为6-18位，只能包含字母数字下划线
         */
        'FORMAT_PASSWORD' => 'password',
        /**
         * 文件名：长度为1-50位，只能包含中文字母数字下划线中划线空格中文括号英文括号
         */
        'FORMAT_FILENAME' => 'filename',
        /**
         * 文件名：长度为1-10位，只能包含中文字母数字
         */
        'FORMAT_VIEWNAME' => 'viewname'
    ]
];

