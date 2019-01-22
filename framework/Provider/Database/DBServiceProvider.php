<?php

namespace Framework\Provider\Database;

use Framework\Provider\ServiceProvider;
use Framework\Service\Database\DB;

class DBServiceProvider extends ServiceProvider {

    protected $blnDefer = true;

    /**
     * 注册服务提供者
     */
    public function register() {
        $this->objApp->singleton(DB::class, DB::class);
    }

    public function provides() {
        return [DB::class];
    }

}
