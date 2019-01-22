<?php

namespace Framework\Service\Http;

use Framework\Contract\Http\Response as ResponseContract;

class JsonResponse implements ResponseContract {

    /**
     * 响应内容
     */
    protected $arrContent = ['success' => 0, 'err_code' => '', 'err_msg' => ''];

    /**
     * 响应头
     */
    protected $arrHeader = [];

    /**
     * 创建响应实例
     * @param array $arrContent 响应内容
     * @param array $arrHeader 响应头
     */
    public function __construct($arrContent = [], $arrHeader = []) {
        $this->arrContent = array_merge($this->arrContent, $arrContent);
        $this->arrHeader = $arrHeader;
    }

    /**
     * 发送响应
     */
    public function send() {
        $this->sendHeader();
        $this->sendContent();
    }

    /**
     * 发送响应头
     */
    protected function sendHeader() {
        foreach ($this->arrHeader as $strName => $arrValue) {
            foreach ($arrValue as $strValue) {
                header($strName . ':' . $strValue);
            }
        }
    }

    /**
     * 发送响应内容
     */
    protected function sendContent() {
        exit(json_encode($this->arrContent));
    }

}
