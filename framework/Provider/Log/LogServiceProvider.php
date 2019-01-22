<?php

namespace Framework\Provider\Log;

use Framework\Provider\ServiceProvider;
use Framework\Service\Log\Log;

class LogServiceProvider extends ServiceProvider {

    /**
     * 注册服务提供者
     */
    public function register() {
        $this->objApp->singleton('log', function () {
            return new Log($this->objApp);
        });
    }

}
