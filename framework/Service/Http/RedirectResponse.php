<?php

namespace Framework\Service\Http;

use Framework\Contract\Http\Response as ResponseContract;

class RedirectResponse implements ResponseContract {

    /**
     * 重定向url
     */
    protected $strUrl = '';

    /**
     * 创建响应实例
     * @param string $strUrl 重定向url
     */
    public function __construct($strUrl) {
        $this->strUrl = $strUrl;
    }

    /**
     * 发送响应
     */
    public function send() {
        $this->redirect();
    }

    /**
     * 发送响应头
     */
    protected function redirect() {
        header('Location:' . $this->strUrl);
        die();
    }

}
