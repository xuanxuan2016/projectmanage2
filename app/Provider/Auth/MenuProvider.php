<?php

namespace App\Provider\Auth;

use App\Service\Auth\Menu;
use Framework\Provider\ServiceProvider;

class MenuProvider extends ServiceProvider {

    protected $blnDefer = true;

    /**
     * 注册服务提供者
     */
    public function register() {
        $this->objApp->singleton('menu', Menu::class);
    }

    public function provides() {
        return ['menu'];
    }

}
