<?php

namespace Framework\Provider;

abstract class ServiceProvider {

    /**
     * 应用实例
     */
    protected $objApp;

    /**
     * 是否为延迟服务提供者
     * @var bool
     */
    protected $blnDefer = false;

    /**
     * 创建服务提供者实例
     */
    public function __construct($objApp) {
        $this->objApp = $objApp;
    }

    /**
     * 当为延迟服务时，获取提供者提供的服务
     * @return array
     */
    public function provides() {
        return [];
    }

    /**
     * 服务提供者是否延迟加载
     * @return bool
     */
    public function isDeferred() {
        return $this->blnDefer;
    }

}
