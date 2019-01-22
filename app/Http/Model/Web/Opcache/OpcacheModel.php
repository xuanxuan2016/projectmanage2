<?php

namespace App\Http\Model\Web\Opcache;

use Framework\Facade\Request;
use Framework\Service\Opcache\Opcache;

class OpcacheModel {

    /**
     * opcache实例
     */
    protected $objOpcache;

    /**
     * 验证key
     */
    protected $strAuth = '51jobms';

    /**
     * 构造函数
     */
    public function __construct(Opcache $objOpcache) {
        $this->objOpcache = $objOpcache;
    }

    /**
     * 重置所有缓存
     */
    public function reset(&$strErrMsg = '') {
        //auth检查
        $strErrMsg = $this->checkAuth();
        if (!empty($strErrMsg)) {
            return false;
        }
        return $this->objOpcache->reset();
    }

    /**
     * 设置脚本缓存
     */
    public function setCache(&$strErrMsg = '') {
        //auth检查
        $strErrMsg = $this->checkAuth();
        if (!empty($strErrMsg)) {
            return false;
        }
        return $this->objOpcache->setCache(Request::getParam('script'));
    }

    /**
     * 删除脚本缓存
     */
    public function getStatus(&$strErrMsg = '') {
        //auth检查
        $strErrMsg = $this->checkAuth();
        if (!empty($strErrMsg)) {
            return [];
        }
        return $this->objOpcache->getStatus();
    }

    /**
     * 删除脚本缓存
     */
    public function delCache(&$strErrMsg = '') {
        //auth检查
        $strErrMsg = $this->checkAuth();
        if (!empty($strErrMsg)) {
            return false;
        }
        return $this->objOpcache->delCache(Request::getParam('script'));
    }

    /**
     * 检查权限信息
     */
    protected function checkAuth() {
        if (Request::getParam('auth') !== $this->strAuth) {
            return 'auth验证失败';
        }
        return '';
    }

}
