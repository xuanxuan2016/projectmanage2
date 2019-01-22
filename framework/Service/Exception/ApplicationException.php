<?php

namespace Framework\Service\Exception;

use Exception;
use Framework\Service\Foundation\Application;
use Framework\Service\Http\ResponseFactory;

/**
 * 应用异常
 */
class ApplicationException extends Exception {

    /**
     * 生成异常的http响应
     * @param Exception $objException
     */
    public function render(Application $objApp, Exception $objException) {
        $mixResponse = ['err_msg' => '应用异常，请稍后再试'];
        return $objApp->make(ResponseFactory::class)->make($mixResponse);
    }

}
