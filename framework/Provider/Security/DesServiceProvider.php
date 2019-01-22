<?php

namespace Framework\Provider\Security;

use Framework\Service\Security\Des;
use Framework\Provider\ServiceProvider;

class DesServiceProvider extends ServiceProvider {

    protected $blnDefer = true;

    /**
     * 注册服务提供者
     */
    public function register() {
        $this->objApp->singleton('des', Des::class);
    }

    public function provides() {
        return ['des'];
    }

}
