<?php

namespace App\Http\Model\Web\Auth;

use Framework\Facade\Des;
use Framework\Facade\Request;
use Framework\Service\Database\DB;

class InterfaceModel {

    /**
     * 数据实例
     */
    protected $objDB;

    /**
     * 构造方法
     */
    public function __construct(DB $objDB) {
        $this->objDB = $objDB;
    }

}
