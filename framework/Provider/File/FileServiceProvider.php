<?php

namespace Framework\Provider\File;

use Framework\Service\File\File;
use Framework\Provider\ServiceProvider;

class FileServiceProvider extends ServiceProvider {

    protected $blnDefer = true;

    /**
     * 注册服务提供者
     */
    public function register() {
        $this->objApp->singleton('file', File::class);
    }

    public function provides() {
        return ['file'];
    }

}
