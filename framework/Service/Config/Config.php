<?php

namespace Framework\Service\Config;

class Config {

    /**
     * 配置文件路径
     */
    protected $arrFile = [];

    /**
     * 配置文件值
     */
    protected $arrValue = [];

    public function __construct() {
        
    }

    /**
     * 设置配置文件路径
     * @param string $strKey 文件名
     * @param string $strFile 文件路径
     */
    public function setFile($strKey, $strFile) {
        $this->arrFile[$strKey] = $strFile;
    }

    /**
     * 设置配置文件值
     * @param string $strKey 文件名
     */
    protected function setValue($strKey) {
        $this->arrValue[$strKey] = isset($this->arrFile[$strKey]) ? require $this->arrFile[$strKey] : [];
    }

    /**
     * 获取配置文件的值，使用[.]的方式
     * @param string $strKey 键值，如app,app.a
     */
    public function get($strKey) {
        $arrKey = explode('.', $strKey);

        //如果配置未加载，则加载
        if (!array_key_exists($arrKey[0], $this->arrValue)) {
            $this->setValue($arrKey[0]);
        }

        //递归获取配置值
        return $this->getValue($arrKey, $this->arrValue);
    }

    /**
     * 递归获取配置值
     */
    protected function getValue($arrKey, $arrValue) {
        //没有值
        if (!isset($arrValue[$arrKey[0]])) {
            return '';
        }

        //查找到最后
        if (count($arrKey) == 1) {
            return isset($arrValue[$arrKey[0]]) ? $arrValue[$arrKey[0]] : '';
        }

        //继续查找
        return $this->getValue(array_slice($arrKey, 1), $arrValue[$arrKey[0]]);
    }

}
