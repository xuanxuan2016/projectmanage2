<?php

namespace Framework\Provider\Database;

use Framework\Provider\ServiceProvider;
use Framework\Service\Database\HashCacheDB;

class HashCacheDBServiceProvider extends ServiceProvider {

    protected $blnDefer = true;

    /**
     * 注册服务提供者
     */
    public function register() {
        $this->objApp->singleton(HashCacheDB::class, HashCacheDB::class);
    }

    public function provides() {
        return [HashCacheDB::class];
    }

}
