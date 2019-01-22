<?php

/**
 * web index
 */
//设置项目根目录
define('BASE_PATH', realpath(__DIR__ . '/../'));

//自动加载类
include __DIR__ . '/../framework/Loader.php';

//应用
$objApp = new Framework\Service\Foundation\Application(realpath(__DIR__ . '/../'));

//内核
$objKernel = $objApp->make(Framework\Service\Foundation\HttpKernel::class);

//请求处理
$objResponse = $objKernel->handle();

//请求响应
$objResponse->send();
