<?php

namespace App\Http\Controller\Console;

use Framework\Facade\Log;
use Framework\Facade\Config;
use Framework\Facade\Cache;
use Framework\Service\Foundation\Controller as ControllerBase;

class TestController extends ControllerBase {

    /**
     * 构造函数
     */
    public function __construct() {
        
    }

    /**
     * 运行任务
     */
    public function run() {
        echo 'haha';
        var_dump(Cache::exec('get','aa'));
        Log::log('fffffffffff');
    }

}
