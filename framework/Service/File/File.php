<?php

namespace Framework\Service\File;

use ZipArchive;
use Framework\Service\Database\DB;

/**
 * 文件类
 */
class File {

    /**
     * Database类
     */
    protected $objDB;

    public function __construct(DB $objDB) {
        $this->objDB = $objDB;
    }

    /**
     * 获取文件夹路径，如果文件夹不存在则创建
     * @param string $strDirPath 文件夹路径
     */
    public function getDirPath($strDirPath) {
        if (!is_dir($strDirPath)) {
            if (@mkdir($strDirPath, 0777, true)) {
                @chmod($strDirPath, 0775);
                return $strDirPath;
            }
        } else {
            return $strDirPath;
        }
        return false;
    }

    /**
     * 保存附件信息
     * @param array $arrParam 参数
     */
    public function saveAttach($arrParam) {
        $strSql = 'insert into attach(attach_id,cname,path,down_del) values(:attach_id,:cname,:path,:down_del)';
        $arrParams = [
            ':attach_id' => $arrParam['attach_id'],
            ':cname' => $arrParam['cname'],
            ':path' => $arrParam['path'],
            ':down_del' => $arrParam['down_del']
        ];
        return $this->objDB->setMainTable('attach')->insert($strSql, $arrParams) > 0 ? true : false;
    }

    /**
     * 获取附件信息
     * @param string $strAttachId 附件id
     */
    public function getAttach($strAttachId) {
        $strSql = 'select cname,path,down_del from attach where attach_id=:attach_id and status=:status';
        $arrParams = [
            ':attach_id' => $strAttachId,
            ':status' => '01'
        ];
        $arrAttachInfo = $this->objDB->setMainTable('attach')->select($strSql, $arrParams);
        return !empty($arrAttachInfo) ? $arrAttachInfo[0] : [];
    }

    /**
     * 创建压缩文件
     * @param array $arrSourceFile 需要压缩的文件数组，key为文件在压缩包里的文件名，value为被压缩的文件路径。['导出文件.xlsx'=>'\www\导出文件.xlsx']
     * @param string $strDestination 压缩到的位置
     */
    public function createZip($arrSourceFile = [], $strDestination = '') {
        //得到有效的文件路径
        $arrValidFiles = [];
        if (is_array($arrSourceFile)) {
            foreach ($arrSourceFile as $zipfilename => $filename) {
                if (file_exists($filename)) {
                    $arrValidFiles[$zipfilename] = $filename;
                }
            }
        }
        //将有效文件添加到压缩文件中
        if (count($arrValidFiles)) {
            $objZip = new ZipArchive();
            if ($objZip->open($strDestination, file_exists($strDestination) ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
                return false;
            }
            //添加文件
            foreach ($arrValidFiles as $zipfilename => $filename) {
                $objZip->addFile($filename, $zipfilename);
            }
            $objZip->close();
            //检查是否压缩成功
            $blnZipSuccess = file_exists($strDestination);
            //压缩成功删除文件
            if ($blnZipSuccess) {
                foreach ($arrValidFiles as $filename) {
                    unlink($filename);
                }
            }
            return $blnZipSuccess;
        } else {
            return false;
        }
    }

}
