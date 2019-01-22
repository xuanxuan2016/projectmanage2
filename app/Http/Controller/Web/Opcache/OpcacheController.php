<?php

namespace App\Http\Controller\Web\Opcache;

use App\Http\Model\Web\Opcache\OpcacheModel;
use Framework\Service\Foundation\Controller as BaseController;

class OpcacheController extends BaseController {

    /**
     * OpcacheModel实例
     */
    protected $objOpcacheModel;

    /**
     * 控制器方法对应的中间件
     * 方法名:方法对应的中间件
     */
    protected $arrMiddleware = [
    ];

    /**
     * 构造函数
     */
    public function __construct(OpcacheModel $objOpcacheModel) {
        $this->objOpcacheModel = $objOpcacheModel;
    }

    /**
     * 重置所有缓存
     */
    public function reset() {
        $blnFlag = $this->objOpcacheModel->reset($strErrMsg);
        return sprintf('opcache缓存，reset：%s，错误信息：%s', $blnFlag ? '成功' : '失败', $strErrMsg);
    }

    /**
     * 重置所有缓存
     */
    public function getStatus() {
        $arrStatus = [];
        $arrStatus = $this->objOpcacheModel->getStatus($strErrMsg);
        return sprintf('opcache缓存，status：%s，错误信息：%s', json_encode($arrStatus), $strErrMsg);
    }

    /**
     * 设置脚本缓存
     */
    public function setCache() {
        $blnFlag = $this->objOpcacheModel->setCache($strErrMsg);
        return sprintf('opcache缓存，setCache：%s，错误信息：%s', $blnFlag ? '成功' : '失败', $strErrMsg);
    }

    /**
     * 删除脚本缓存
     */
    public function delCache() {
        $blnFlag = $this->objOpcacheModel->delCache($strErrMsg);
        return sprintf('opcache缓存，delCache：%s，错误信息：%s', $blnFlag ? '成功' : '失败', $strErrMsg);
    }

}
