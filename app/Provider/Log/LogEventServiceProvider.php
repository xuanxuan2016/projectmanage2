<?php

namespace App\Provider\Log;

use App\Service\Log\LogEvent;
use Framework\Provider\ServiceProvider;

class LogEventServiceProvider extends ServiceProvider {

    protected $blnDefer = true;

    /**
     * 注册服务提供者
     */
    public function register() {
        $this->objApp->singleton('logevent', LogEvent::class);
    }

    public function provides() {
        return ['logevent'];
    }

}
