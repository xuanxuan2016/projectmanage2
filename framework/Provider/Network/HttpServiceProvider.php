<?php

namespace Framework\Provider\Network;

use Framework\Service\Network\Http;
use Framework\Provider\ServiceProvider;

class HttpServiceProvider extends ServiceProvider {

    protected $blnDefer = true;

    /**
     * 注册服务提供者
     */
    public function register() {
        $this->objApp->singleton('http', Http::class);
    }

    public function provides() {
        return ['http'];
    }

}
