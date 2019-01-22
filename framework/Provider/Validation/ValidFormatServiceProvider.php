<?php

namespace Framework\Provider\Validation;

use Framework\Provider\ServiceProvider;
use Framework\Service\Validation\ValidFormat;

class ValidFormatServiceProvider extends ServiceProvider {

    protected $blnDefer = true;

    /**
     * 注册服务提供者
     */
    public function register() {
        $this->objApp->singleton(ValidFormat::class, ValidFormat::class);
    }

    public function provides() {
        return [ValidFormat::class];
    }

}
