<?php

namespace Framework\Service\Exception;

use Exception;
use Framework\Facade\Log;
use Framework\Facade\Config;
use Framework\Service\Foundation\Application;
use Framework\Service\Http\ResponseFactory;

/**
 * 控制器异常
 */
class ControllerException extends Exception {

    /**
     * 异常记录
     * @param Application $objApp
     * @param Exception $objException
     */
    public function report(Application $objApp, Exception $objException) {
        $strMessage = $objException->getMessage();
        $mixMessage = json_decode($strMessage, true);
        if (is_array($mixMessage) && isset($mixMessage['err_msg'])) {
            $strMessage = $mixMessage['err_msg'];
        }
        $strMessage = $strMessage . '，uri：' . $objApp->make('request')->getUri();
        Log::log($strMessage, Config::get('const.Log.LOG_ERR'));
    }

    /**
     * 生成异常的http响应
     * @param Application $objApp
     * @param Exception $objException
     */
    public function render(Application $objApp, Exception $objException) {
        $mixMessage = json_decode($objException->getMessage(), true);
        if (is_array($mixMessage)) {
            $mixResponse = ['err_msg' => $mixMessage['err_msg']];
        } else {
            //需要重定向
            $mixResponse = $objApp->make('config')->get('web.redirect.controller_wrong');
        }
        return $objApp->make(ResponseFactory::class)->make($mixResponse);
    }

}
