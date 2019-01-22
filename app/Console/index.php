<?php

/**
 * console index
 */
//设置项目根目录
define('BASE_PATH', realpath(__DIR__ . '/../../'));

//自动加载类
include __DIR__ . '/../../framework/Loader.php';

//应用
$objApp = new Framework\Service\Foundation\Application(realpath(__DIR__ . '/../../'));

//内核
$objKernel = $objApp->make(Framework\Service\Foundation\ConsoleKernel::class, ['objRequest' => new \Framework\Service\Http\ConsoleRequest($argv)]);

//任务处理
$objKernel->handle();
