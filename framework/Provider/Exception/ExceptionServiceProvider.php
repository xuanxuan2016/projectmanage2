<?php

namespace Framework\Provider\Exception;

use Framework\Provider\ServiceProvider;
use Framework\Service\Exception\ExceptionHandler;
use Framework\Contract\Exception\ExceptionHandler as ExceptionHandlerContract;

class ExceptionServiceProvider extends ServiceProvider {

    /**
     * 注册服务提供者
     */
    public function register() {
        $this->objApp->singleton(ExceptionHandlerContract::class, ExceptionHandler::class);
    }

}
