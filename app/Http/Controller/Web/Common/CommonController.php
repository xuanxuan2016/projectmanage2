<?php

namespace App\Http\Controller\Web\Common;

use App\Http\Model\Web\Common\CommonModel;
use Framework\Service\Foundation\Controller as BaseController;

class CommonController extends BaseController {

    /**
     * common实例
     */
    protected $objCommonModel;

    /**
     * 控制器方法对应的中间件
     * 方法名:方法对应的中间件
     */
    protected $arrMiddleware = [
    ];

    /**
     * 依赖注入，使用外部类
     */
    public function __construct(CommonModel $objCommonModel) {
        $this->objCommonModel = $objCommonModel;
    }

    /**
     * 获取视图模板里填充的数据
     * 模板,内容,js,css
     */
    protected function getViewData() {
        return [
        ];
    }

    /**
     * 下载文件
     */
    public function downloadFile() {
        $strErrMsg = '';
        $strAttachId = '';
        $blnFlag = $this->objCommonModel->downloadFile($strErrMsg, $strAttachId);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'attach_id' => $strAttachId];
    }

    /**
     * 上传文件
     */
    public function uploadFile() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objCommonModel->uploadFile($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

}
