<?php

namespace Framework\Service\Http;

use Framework\Contract\Http\Response as ResponseContract;

class HtmlResponse implements ResponseContract {

    /**
     * 响应内容
     */
    protected $strContent = '';

    /**
     * 创建响应实例
     * @param string $arrContent 响应内容
     */
    public function __construct($strContent = '') {
        $this->strContent = $strContent;
    }

    /**
     * 发送响应
     */
    public function send() {
        $this->sendContent();
    }

    /**
     * 发送响应内容
     */
    protected function sendContent() {
        exit($this->strContent);
    }

}
