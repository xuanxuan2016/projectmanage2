<?php

namespace Framework\Service\Foundation;

use Framework\Facade\Log;
use Framework\Facade\View;
use Framework\Service\Foundation\Pipeline;
use Framework\Service\Exception\ControllerException;

abstract class Controller {

    /**
     * 应用实例
     */
    protected $objApp;

    /**
     * 请求实例
     */
    protected $objRequest;

    /**
     * 控制器方法对应的中间件
     * 方法名:方法对应的中间件组，如果为数组则后面的为传入到中间件的参数
     * ['funcname'=>[Middleware::class]]
     * ['funcname'=>[[Middleware::class,...params]]]
     */
    protected $arrMiddleware = [
    ];

    /**
     * 获取视图模板里填充的数据
     * 模板,内容,js,css
     */
    protected function getViewData() {
        return [];
    }

    /**
     * 显示视图
     */
    protected function view() {
        //获取数据
        $arrData = $this->getViewData();
        //生成页面
        return View::make($arrData);
    }

    /**
     * 调用控制器的方法
     */
    public function callAction($strMethod, $objApp, $objRequest, $arrArguments = []) {
        $this->objApp = $objApp;
        $this->objRequest = $objRequest;
        //获取中间件
        $arrMiddleware = $this->getMiddleware($strMethod);
        //调用中间件
        return (new Pipeline($objApp))
                        ->send($objRequest)
                        ->through($arrMiddleware)
                        ->then(function() use ($strMethod, $arrArguments) {
                            //执行方法
                            return call_user_func_array([$this, $strMethod], $arrArguments);
                        });
    }

    /**
     * 获取方法对应的中间件
     */
    protected function getMiddleware($strMethod) {
        $strMethod = strtolower($strMethod);
        $this->arrMiddleware = array_change_key_case($this->arrMiddleware);
        return isset($this->arrMiddleware[$strMethod]) ? $this->arrMiddleware[$strMethod] : [];
    }

    /**
     * 控制器没有对应的方法
     */
    public function __call($strMethod, $arrArguments) {
        throw new ControllerException(json_encode(['err_msg' => '请求操作错误']));
    }

}
