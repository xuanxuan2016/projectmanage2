<?php

namespace Framework\Provider\Image;

use Framework\Service\Image\Image;
use Framework\Provider\ServiceProvider;

class ImageServiceProvider extends ServiceProvider {

    protected $blnDefer = true;

    /**
     * 注册服务提供者
     */
    public function register() {
        $this->objApp->singleton('image', Image::class);
    }

    public function provides() {
        return ['image'];
    }

}
