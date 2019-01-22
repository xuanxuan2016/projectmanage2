<?php

namespace Framework\Contract\Exception;

use Exception;

/**
 * 异常处理接口
 */
interface ExceptionHandler {

    /**
     * 记录异常
     * @param Exception $objException
     */
    public function report(Exception $objException);

    /**
     * 生成异常的http响应
     * @param Exception $objException
     */
    public function render(Exception $objException);
}
