<?php

namespace Framework\Provider\Sms;

use Framework\Service\Sms\Sms;
use Framework\Provider\ServiceProvider;

class SmsServiceProvider extends ServiceProvider {

    protected $blnDefer = true;

    /**
     * 注册服务提供者
     */
    public function register() {
        $this->objApp->singleton('sms', Sms::class);
    }

    public function provides() {
        return ['sms'];
    }

}
