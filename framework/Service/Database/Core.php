<?php

namespace Framework\Service\Database;

use Framework\Service\Database\DB;

class Core {

    /**
     * 数据库实例
     */
    protected $objDB;

    /**
     * 构造函数
     */
    public function __construct(DB $objDB) {
        $this->objDB = $objDB;
    }

    /**
     * 根据业务类型获取数据库信息
     * @param string $strBusinessType 业务类型
     */
    public function getBusinessInfo($strBusinessType) {
        return [];
    }

    /**
     * 根据表名获取表信息
     * @param string $strTableName 表名
     * @return array 表信息 
     */
    public function getTableInfo($strTableName) {
        return [];
    }

}
