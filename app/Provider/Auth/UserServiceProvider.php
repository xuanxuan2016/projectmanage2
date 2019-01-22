<?php

namespace App\Provider\Auth;

use App\Service\Auth\User;
use Framework\Provider\ServiceProvider;
use App\Contract\Auth\User as UserContract;

class UserServiceProvider extends ServiceProvider {

    protected $blnDefer = true;

    /**
     * 注册服务提供者
     */
    public function register() {
        $this->objApp->singleton(UserContract::class, User::class);
    }

    public function provides() {
        return [UserContract::class];
    }

}
