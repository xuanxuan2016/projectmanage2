<?php

namespace Framework\Service\Foundation;

/**
 * 别名加载
 */
class AliasLoader {

    /**
     * 实例
     */
    protected static $objInstance;

    /**
     * facade类
     */
    protected $arrAlias = [];

    private function __construct($arrAlias) {
        $this->arrAlias = $arrAlias;
    }

    private function __clone() {
        
    }

    private function __wakeup() {
        
    }

    /**
     * 获取实例
     */
    public static function getInstance($arrAlias) {
        if (is_null(static::$objInstance)) {
            static::$objInstance = new static($arrAlias);
        }

        return static::$objInstance;
    }

    /**
     * 注册自动加载
     */
    public function registerAutoLoad() {
        spl_autoload_register([$this, 'load'], true, true);
    }

    /**
     * 加载类
     */
    public function load($strAlias) {
        if (isset($this->arrAlias[$strAlias])) {
            class_alias($this->arrAlias[$strAlias], $strAlias);
        }
    }

}
