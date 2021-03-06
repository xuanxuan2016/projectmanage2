<?php

namespace App\Exception;

use Exception;
use Framework\Service\Http\ResponseFactory;
use Framework\Service\Foundation\Application;

/**
 * 登录异常(小程序)
 */
class AuthMiniException extends Exception {

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
        $mixException = $objException->getMessage();
        $mixException = json_decode($mixException, true);
        return $objApp->make(ResponseFactory::class)->make($mixException);
    }

}
