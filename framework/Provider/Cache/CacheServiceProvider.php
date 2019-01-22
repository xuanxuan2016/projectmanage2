<?php

namespace Framework\Provider\Cache;

use Framework\Provider\ServiceProvider;
use Framework\Contract\Cache\Cache as CacheContract;
use Framework\Service\Cache\CacheRedis;

class CacheServiceProvider extends ServiceProvider {

    protected $blnDefer = true;

    /**
     * 注册服务提供者
     */
    public function register() {
        $this->objApp->singleton(CacheContract::class, CacheRedis::class);
    }

    public function provides() {
        return [CacheContract::class];
    }

}
