<?php

namespace Framework\Service\File;

use Framework\Facade\App;
use Framework\Facade\Log;
use Framework\Facade\File;
use Framework\Facade\Config;

/**
 * 文件上传类
 */
class UploadFile {

    /**
     * 默认的有效后缀名
     */
    protected $arrValidExt = ['doc', 'docx', 'xls', 'xlsx', 'pdf', 'jpg', 'png', 'rar', 'zip', 'txt', '7z', 'msg', 'jpeg'];

    /**
     * 默认的文件大小限制2M
     */
    protected $intValidSize = 2 * 1024 * 1024;

    /**
     * 上传校验规则
     * ext:有效后缀名
     * size:允许大小
     */
    protected $arrCheckRule = [];

    /**
     * 返回信息
     */
    protected $arrReturn = ['success' => 0, 'err_msg' => '', 'attach_id' => '', 'attach_name' => '', 'attach_path' => ''];

    /**
     * 构造函数
     */
    public function __construct() {
        
    }

    /**
     * 初始化对象
     * @param array $arrCheckRule 上传校验规则
     * <br>ext:[]，有效后缀名
     * <br>size:int，允许大小
     * @return $this
     */
    public function init($arrCheckRule = []) {
        $this->arrCheckRule = [
            'ext' => isset($arrCheckRule['ext']) && is_array($arrCheckRule['ext']) ? $arrCheckRule['ext'] : $this->arrValidExt,
            'size' => isset($arrCheckRule['size']) ? $arrCheckRule['size'] : $this->intValidSize
        ];
        return $this;
    }

    /**
     * 上传
     */
    public function upload() {
        //是否有上传信息
        if (!isset($_FILES["file"]) || $_FILES["file"]["error"] != UPLOAD_ERR_OK) {
            $this->arrReturn['err_msg'] = '上传文件错误,code=1';
            return $this->arrReturn;
        }

        //文件名检验
        $strName = $_FILES["file"]["name"];
        $arrExt = explode('.', $strName);
        unset($arrExt[count($arrExt) - 1]);
        if (!checkFormat(implode('.', $arrExt), Config::get('const.ValidFormat.FORMAT_FILENAME'))) {
            $this->arrReturn['err_msg'] = '上传文件错误，文件名长度为1-50位，只能包含中文字母数字下划线中划线空格中文括号英文括号';
            return $this->arrReturn;
        }

        //后缀名校验
        $arrExt = explode('.', $strName);
        $strExt = strtolower($arrExt[count($arrExt) - 1]);
        if (!in_array($strExt, $this->arrCheckRule['ext'])) {
            $this->arrReturn['err_msg'] = '上传文件错误，文件格式需要为' . implode(',', $this->arrCheckRule['ext']);
            return $this->arrReturn;
        }

        //文件大小校验
        $intSize = $_FILES["file"]["size"];
        if ($intSize > $this->arrCheckRule['size']) {
            $this->arrReturn['err_msg'] = '上传文件错误，文件大小需小于' . ($this->arrCheckRule['size'] / 1024) . 'KB';
            return $this->arrReturn;
        }

        //转移文件
        $strAttachId = getGUID();
        $strAttachDir = File::getDirPath(App::make('path.storage') . '/upload/' . $strExt . '/' . date('Y') . '/' . date('m') . '/');
        $strAttachPath = $strAttachDir . $strAttachId . '.' . $strExt;
        if (!move_uploaded_file($_FILES["file"]["tmp_name"], $strAttachPath)) {
            $this->arrReturn['err_msg'] = '上传文件错误,code=2';
            return $this->arrReturn;
        }

        //信息入库
        $arrParam = [
            'attach_id' => $strAttachId,
            'cname' => $strName,
            'path' => $strAttachPath,
            'down_del' => 0
        ];
        if (!File::saveAttach($arrParam)) {
            $this->arrReturn['err_msg'] = '上传文件错误,code=3';
            return $this->arrReturn;
        }

        //返回
        $this->arrReturn = [
            'success' => 1,
            'attach_id' => $strAttachId,
            'attach_name' => $strName,
            'attach_path' => $strAttachPath
        ];
        return $this->arrReturn;
    }

}
