<?php

namespace Framework\Service\Exception;

use Exception;
use Framework\Facade\Config;
use Framework\Facade\Request;
use Framework\Service\Foundation\Application;
use Framework\Service\Http\ResponseFactory;
use Framework\Contract\Exception\ExceptionHandler as ExceptionHandlerContract;

class ExceptionHandler implements ExceptionHandlerContract {

    protected $objApp;

    public function __construct(Application $objApp) {
        $this->objApp = $objApp;
    }

    /**
     * 异常记录
     * @param Exception $objException
     */
    public function report(Exception $objException) {
        //如果异常有自己的处理方法
        if (method_exists($objException, 'report')) {
            return $objException->report($this->objApp, $objException);
        }

        //日志记录
        $this->objApp->make('log')->log($objException->getMessage(), Config::get('const.Log.LOG_ERR'));
    }

    /**
     * 生成异常的http响应
     * @param type $objRequest
     * @param Exception $objException
     */
    public function render(Exception $objException) {
        //如果异常有自己的处理方法
        if (method_exists($objException, 'render')) {
            return $objException->render($this->objApp, $objException);
        }

        //生成标准响应
        $mixResponse = ['err_msg' => '请求错误，请稍后再试'];
        return $this->objApp->make(ResponseFactory::class)->make($mixResponse);
    }

}
