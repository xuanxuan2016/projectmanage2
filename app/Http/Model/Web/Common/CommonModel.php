<?php

namespace App\Http\Model\Web\Common;

use Framework\Facade\Image;
use Framework\Facade\Request;
use Framework\Service\File\UploadFile;

class CommonModel {

    /**
     * UploadFile实例
     */
    protected $objUploadFile;

    /**
     * 构造方法
     */
    public function __construct(UploadFile $objUploadFile) {
        $this->objUploadFile = $objUploadFile;
    }

    // -------------------------------------- downloadFile -------------------------------------- //

    /**
     * 下载文件
     */
    public function downloadFile(&$strErrMsg, &$strAttachId) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkDownloadFile($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        //4.结果返回
        $strAttachId = $arrParam['attach_id'];
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkDownloadFile(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        if (empty($arrParam['attach_id'])) {
            return '附件不存在';
        }

        //3.字段数据库配置检查
        //4.业务检查
    }

    // -------------------------------------- uploadFile -------------------------------------- //

    /**
     * 上传文件
     */
    public function uploadFile(&$strErrMsg, &$arrData) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = '';
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        //4.结果返回
        $arrReturn = $this->objUploadFile->init(['ext' => ['png', 'jpeg'], 'size' => 2 * 1024 * 1024])->upload();
        if ($arrReturn['success'] == 0) {
            $strErrMsg = $arrReturn['err_msg'];
            return false;
        } else {
            $arrData = [
                'base64' => Image::getImageBase64($arrReturn['attach_path'])['encode']
            ];
            return true;
        }
    }

}
