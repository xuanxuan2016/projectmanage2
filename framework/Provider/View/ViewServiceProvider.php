<?php

namespace Framework\Provider\View;

use Framework\Service\View\View;
use Framework\Provider\ServiceProvider;

class ViewServiceProvider extends ServiceProvider {

    protected $blnDefer = true;

    /**
     * 注册服务提供者
     */
    public function register() {
        $this->objApp->singleton('view', View::class);
    }

    public function provides() {
        return ['view'];
    }

}
