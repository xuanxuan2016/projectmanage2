<?php

namespace App\Exception;

use Exception;
use Framework\Service\Http\ResponseFactory;
use Framework\Service\Foundation\Application;

/**
 * 登录异常
 */
class AuthException extends Exception {

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
        if (is_array($mixException) && !empty($mixException['redirect_url'])) {
            $mixResponse = $mixException['redirect_url'];
        } else {
            $mixResponse = $objApp->make('config')->get('web.redirect.auth_wrong');
        }
        return $objApp->make(ResponseFactory::class)->make($mixResponse);
    }

}
