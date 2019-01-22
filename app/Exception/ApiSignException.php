<?php

namespace App\Exception;

use Exception;
use Framework\Service\Http\ResponseFactory;
use Framework\Service\Foundation\Application;

/**
 * 接口校验异常
 */
class ApiSignException extends Exception {

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
        $mixResponse = ['err_msg' => $objException->getMessage()];
        return $objApp->make(ResponseFactory::class)->make($mixResponse);
    }

}
