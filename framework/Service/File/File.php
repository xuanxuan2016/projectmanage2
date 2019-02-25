<?php

namespace Framework\Service\File;

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
        return $this->objDB->setMainTable('attach')->insert($strSql, $arrParams);
    }

    /**
     * 保存附件信息
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

}
