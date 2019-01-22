<?php

/**
 * 网站基本的配置
 */
return [
    /**
     * 外观
     * 1.提供类中方法的静态调用
     */
    'facade' => [
        /**
         * 框架-立即加载
         */
        'Config' => Framework\Facade\Config::class,
        'Log' => Framework\Facade\Log::class,
        'Request' => Framework\Facade\Request::class,
        'User' => Framework\Facade\User::class,
        'App' => Framework\Facade\App::class,
        /**
         * 框架-延迟加载
         */
        'Cache' => Framework\Facade\Cache::class,
        'Des' => Framework\Facade\Des::class,
        'Image' => Framework\Facade\Image::class,
        'Http' => Framework\Facade\Http::class,
        'Sms' => Framework\Facade\Sms::class,
        /**
         * 应用-延迟加载
         */
        'LogEvent' => App\Facade\LogEvent::class,
        'Menu' => App\Facade\Menu::class
    ],
    /**
     * 服务提供者
     * 1.单例的绑定
     * 2.接口到实现的绑定
     * 3.实例的绑定
     * 
     * 其它类的使用方式，可不通过服务提供者
     */
    'provider' => [
        /**
         * 框架
         */
        Framework\Provider\Cache\CacheServiceProvider::class,
        Framework\Provider\View\ViewServiceProvider::class,
        Framework\Provider\Security\DesServiceProvider::class,
        Framework\Provider\Network\HttpServiceProvider::class,
        Framework\Provider\Validation\ValidFormatServiceProvider::class,
        Framework\Provider\Database\DBServiceProvider::class,
        Framework\Provider\Database\HashCacheDBServiceProvider::class,
        Framework\Provider\Image\ImageServiceProvider::class,
        Framework\Provider\Sms\SmsServiceProvider::class,
        /**
         * 应用
         */
        App\Provider\Log\LogEventServiceProvider::class,
        App\Provider\Auth\UserServiceProvider::class,
        App\Provider\Auth\MenuProvider::class
    ],
    /**
     * 中间件
     * 1.http请求需要通过的检查
     * 2.按顺序检查
     * 3.按照uri中第一个/前进行匹配
     */
    'middleware' => [
        /**
         * 所有请求都需经过
         */
        'all' => [
            Framework\Service\Foundation\Middleware\All\CheckUri::class
        ],
        /**
         * api请求需要经过
         */
        'api' => [
            App\Http\Middleware\Api\CheckSign::class
        ],
        /**
         * web请求需要经过
         * 1.非登记的二级域名，都算web
         */
        'web' => [
            App\Http\Middleware\Web\CheckAuth::class
        ],
        /**
         * 小程序请求需要经过
         */
        'miniapi' => [
            App\Http\Middleware\MiniApi\CheckAuth::class
        ],
        /**
         * 控制台程序不经过中间件处理
         */
        'console' => [
        ]
    ],
    /**
     * 显式的二级目录
     */
    'second_dir' => ['api', 'miniapi', 'web'],
    /**
     * uri解析控制器规则
     * 1.配置的二级目录，uri必须包含控制器与控制器方法
     */
    'uri_resolve_rule' => ['api', 'miniapi'],
    /**
     * web/api路由
     * 1.配置uri对应的控制器
     * 2.如果没配置，则使用默认uri结构处理
     */
    'route' => [
    ],
    /**
     * 视图
     * 1.配置uri对应的视图
     * 2.如果没配置，则使用默认uri结构处理
     */
    'view' => [
    ],
    /**
     * 控制台路由
     * 1.配置uri对应的控制器
     */
    'console_route' => [
        'test' => 'Console\TestController@run'
    ]
];

