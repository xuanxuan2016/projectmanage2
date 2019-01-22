<?php

namespace Framework\Service\Foundation;

use Exception;
use Framework\Facade\Config;
use Framework\Contract\Http\Request;
use Framework\Service\Foundation\Router;
use Framework\Service\Foundation\Pipeline;
use Framework\Contract\Exception\ExceptionHandler as ExceptionHandlerContract;

class ConsoleKernel {

    /**
     * 应用实例
     */
    protected $objApp;

    /**
     * 路由实例
     */
    protected $objRouter;

    /**
     * 请求实例
     */
    protected $objRequest;

    /**
     * 启动的类
     */
    protected $arrBootstrappers = [
        BootStrap\LoadConfiguration::class,
        BootStrap\HandleExceptions::class,
        BootStrap\RegisterFacades::class,
        BootStrap\RegisterProviders::class
    ];

    /**
     * 创建http
     */
    public function __construct(Application $objApp, Router $objRouter, Request $objRequest) {
        $this->objApp = $objApp;
        $this->objRouter = $objRouter;
        $this->objRequest = $objRequest;
    }

    /**
     * 处理http请求
     */
    public function handle() {
        try {
            $this->bootstrap();
            $this->dispatch();
        } catch (Exception $objException) {
            $this->reportException($objException);
        }
    }

    /**
     * 应用启动
     */
    protected function bootstrap() {
        $this->objApp->bootstrapWith($this->arrBootstrappers);
    }

    /**
     * 路由分发
     */
    protected function dispatch() {
        $this->objApp->instance('request', $this->objRequest);

        $arrMiddleware = [];

        return (new Pipeline($this->objApp))
                        ->send($this->objRequest)
                        ->through($arrMiddleware)
                        ->then($this->getRouterRun());
    }

    /**
     * 获取运行路由方法
     */
    protected function getRouterRun() {
        return function($objRequest) {
            $this->objApp->instance('request', $objRequest);
            return $this->objRouter->runRoute($objRequest);
        };
    }

    /**
     * 报告异常
     * @param Exception $objException
     */
    protected function reportException(Exception $objException) {
        $this->objApp->make(ExceptionHandlerContract::class)->report($objException);
    }

}
