<?php

namespace Framework\Service\Http;

use Framework\Contract\Http\Request as RequestContract;

/**
 * console请求
 */
class ConsoleRequest implements RequestContract {

    /**
     * argv
     */
    protected $arrParam = [];

    /**
     * uri
     */
    protected $strUri = '';

    /**
     * 每次请求的唯一标识
     */
    protected $strRequestID = '';

    /**
     * 创建请求实例
     */
    public function __construct($argv = []) {
        $this->init($argv);
    }

    /**
     * 初始化数据
     */
    protected function init($argv) {
        $this->strUri = count($argv) >= 2 ? $argv[1] : '';
        $this->arrParam = array_slice($argv, 2);
        $this->setRequestID(getGUID());
    }

    /**
     * 获取单个参数
     * @param string $strParamName 参数名
     * @param string $strDefault 当获取不到参数时，返回的默认值
     */
    public function getParam($strParamName, $strDefault = '') {
        return $strDefault;
    }

    /**
     * 获取所有参数
     */
    public function getAllParam() {
        return $this->arrParam;
    }

    /**
     * 获取cookie
     * @param string $strCookieName cookie名
     */
    public function getCookie($strCookieName) {
        return '';
    }

    /**
     * 设置cookie
     * @param string $strCookieName cookie名称
     * @param string $strCookieValue cookie值
     * @param int $intExpire 超时时间，默认为会话结束
     * @param string $strDomain cookie的有效域名/子域名，默认当前域名
     * @param string $strPath cookie作用域，默认根目录
     */
    public function setCookie($strCookieName, $strCookieValue = '', $intExpire = 0, $strDomain = '', $strPath = '/') {
        return true;
    }

    /**
     * 删除cookie
     * @param string $strCookieName cookie名称
     * @param string $strDomain cookie的有效域名/子域名，默认当前域名
     * @param string $strPath cookie作用域
     */
    public function delCookie($strCookieName, $strDomain = '', $strPath = '/') {
        return true;
    }

    /**
     * 获取uri
     */
    public function getUri() {
        return strtolower($this->strUri);
    }

    /**
     * 判断是否ajax请求
     */
    public function isAjax() {
        return true;
    }

    /**
     * 获取请求的二级目录
     */
    public function getSecondDir() {
        return 'console';
    }

    /**
     * 获取客户端ip
     */
    public function getClientIP() {
        $strIP = '';
        $strIpCmd = "ifconfig eth0 | sed -n '/inet addr/p' | awk '{print $2}' | awk -F ':' '{print $2}'";
        $strIP = trim(exec($strIpCmd));
        return $strIP;
    }

    /**
     * 获取服务端ip
     */
    public function getServerIP() {
        $strIP = '';
        $strIpCmd = "ifconfig eth0 | sed -n '/inet addr/p' | awk '{print $2}' | awk -F ':' '{print $2}'";
        $strIP = trim(exec($strIpCmd));
        return $strIP;
    }

    /**
     * 获取请求标识id(guid)
     */
    public function setRequestID($strRequestID) {
        $this->strRequestID = $strRequestID;
    }

    /**
     * 获取请求标识id
     */
    public function getRequestID() {
        return $this->strRequestID;
    }

}
