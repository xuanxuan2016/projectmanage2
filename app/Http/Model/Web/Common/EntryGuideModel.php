<?php

namespace App\Http\Model\Web\Common;

use Framework\Facade\App;
use Framework\Facade\Request;
use Framework\Service\Database\DB;
use Framework\Service\MarkDown\HyperDown;

class EntryGuideModel {

    /**
     * 数据实例
     */
    protected $objDB;

    /**
     * HyperDown实例
     */
    protected $objHyperDown;

    /**
     * 构造方法
     */
    public function __construct(DB $objDB, HyperDown $objHyperDown) {
        $this->objDB = $objDB;
        $this->objHyperDown = $objHyperDown;
    }

    /**
     * 获取html内容
     */
    public function getHtml() {
        $strFilePath = App::make('path.resource') . '/markdown/入职指南.md';
        return $this->objHyperDown->makeHtml(file_get_contents($strFilePath));
    }

}
