<?php

namespace Framework\Service\Http;

use Framework\Facade\App;
use Framework\Facade\File;
use Framework\Service\File\Excel\ExcelWrite;
use Framework\Contract\Http\Response as ResponseContract;

class FileResponse implements ResponseContract {

    /**
     * 附件id
     */
    protected $strAttachId;

    /**
     * ExcelWrite实例
     */
    protected $objExcelWrite;

    /**
     * 创建响应实例
     * @param string $strAttachId 附件id
     */
    public function __construct($strAttachId) {
        $this->strAttachId = $strAttachId;
        $this->objExcelWrite = App::make(ExcelWrite::class);
    }

    /**
     * 发送响应
     */
    public function send() {
        //1.获取附件信息
        $arrAttach = File::getAttach($this->strAttachId);
        if (empty($arrAttach)) {
            exit;
        }
        //2.输出文件
        $strFileName = $arrAttach['path'];
        //解决中文文件名乱码
        $strSaveName = iconv('utf-8', 'gb2312', $arrAttach['cname']);
        if (file_exists($strFileName)) {
            ob_end_clean();
            $objFile = fopen($strFileName, "rb");
            Header("Content-type: application/octet-stream");
            Header("Content-Transfer-Encoding: binary");
            Header("Accept-Ranges: bytes");
            Header("Content-Length:" . filesize($strFileName));
            Header("Content-Disposition: attachment;filename=" . $strSaveName); //filename前面的分号不注意写成了冒号，导致错误
            while (!feof($objFile)) {
                echo fread($objFile, 32768);
            }
            fclose($objFile);
            //下载好后是否删除文件
            if ($arrAttach['down_del'] == 1) {
                unlink($strFileName);
            }
            exit;
        }
    }

}
