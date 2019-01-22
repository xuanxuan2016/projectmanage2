<?php

namespace Framework\Service\Foundation;

use Closure;
use Framework\Service\Foundation\Application;

class Pipeline {

    /**
     * 应用实例
     */
    protected $objApp;

    /**
     * 通过管道处理的对象
     */
    protected $mixPassable;

    /**
     * 通过的管道
     */
    protected $arrPipes = [];

    /**
     * 管道的处理方法
     */
    protected $strMethod = 'handle';

    /**
     * 创建管道实例
     */
    public function __construct(Application $objApp = null) {
        $this->objApp = $objApp;
    }

    /**
     * 设置通过管道发送的对象
     */
    public function send($mixPassable) {
        $this->mixPassable = $mixPassable;
        return $this;
    }

    /**
     * 设置管道
     */
    public function through($arrPipes) {
        $this->arrPipes = $arrPipes;
        return $this;
    }

    /**
     * 拼接最终的回调函数(按中间件顺序执行)
     */
    public function then(Closure $mixDestination) {
        //生成中间件的嵌套回调，按$this->arrPipes，最后执行prepareDestination中的闭包方法
        $mixPipeline = array_reduce(
                array_reverse($this->arrPipes), $this->carry(), $this->prepareDestination($mixDestination)
        );
        //运行嵌套的回调
        return $mixPipeline($this->mixPassable);
    }

    /**
     * 获取闭包(洋葱)的最后一块
     */
    protected function prepareDestination(Closure $mixDestination) {
        return function ($mixPassable) use ($mixDestination) {
            return $mixDestination($mixPassable);
        };
    }

    /**
     * 获取每个中间件的调用闭包
     */
    protected function carry() {
        return function ($mixStack, $mixPipe) {
            /**
             * $mixStack：嵌套的闭包
             * $mixPipe：中间件类名或闭包
             */
            return function ($mixPassable) use ($mixStack, $mixPipe) {
                $arrUserParam = [];
                if (is_array($mixPipe)) {
                    //如果是数组，解析中间件与参数
                    $arrUserParam = array_slice($mixPipe, 1);
                    $mixPipe = $mixPipe[0];
                }
                if (is_callable($mixPipe)) {
                    //如果通道是闭包，则直接运行
                    return $mixPipe($mixPassable, $mixStack, ...$arrUserParam);
                } elseif (!is_object($mixPipe)) {
                    //如果通道不是对象，需要进行解析
                    $mixPipe = $this->objApp->make($mixPipe);
                    //获取传入执行方法的参数
                    $arrParameters = array_merge([$mixPassable, $mixStack], $arrUserParam);
                } else {
                    //获取传入执行方法的参数
                    $arrParameters = array_merge([$mixPassable, $mixStack], $arrUserParam);
                }
                //调用中间件中的指定方法或执行__invoke魔术方法
                return method_exists($mixPipe, $this->strMethod) ? $mixPipe->{$this->strMethod}(...$arrParameters) : $mixPipe(...$arrParameters);
            };
        };
    }

}
