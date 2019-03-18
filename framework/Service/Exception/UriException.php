<?php

namespace Framework\Service\Exception;

use Exception;
use Framework\Facade\Request;
use Framework\Service\Foundation\Application;
use Framework\Service\Http\ResponseFactory;

/**
 * 访问异常
 * 1.uri格式错误
 * 2.定位不到控制器
 */
class UriException extends Exception {

    /**
     * 异常记录
     * @param Application $objApp
     * @param Exception $objException
     */
    public function report(Application $objApp, Exception $objException) {
        
    }

    /**
     * 生成异常的http响应
     * @param Application $objApp
     * @param Exception $objException
     */
    public function render(Application $objApp, Exception $objException) {
        $mixMessage = json_decode($objException->getMessage(), true);
        if (empty(Request::getUri())) {
            //跳转home页
            $mixResponse = $objApp->make('config')->get('web.redirect.uri_empty');
        } else if (is_array($mixMessage)) {
            $mixResponse = ['err_msg' => $arrMessage['err_msg']];
        } else {
            //需要重定向
            $mixResponse = $objApp->make('config')->get('web.redirect.uri_wrong');
        }
        return $objApp->make(ResponseFactory::class)->make($mixResponse);
    }

}
