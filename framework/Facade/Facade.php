<?php

namespace Framework\Facade;

/**
 * 外观
 */
abstract class Facade {

    /**
     * 应用实例
     */
    protected static $objApp;

    /**
     * 已解析过的外观
     */
    protected static $resolvedInstance;

    /**
     * 获取外观实例
     */
    public static function getFacadeInstance() {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    /**
     * 是否需要重新解析实例
     */
    public static function isReMake() {
        return false;
    }

    /**
     * 获取外观名称
     */
    protected static function getFacadeAccessor() {
        throw new Exception('获取外观名称错误');
    }

    /**
     * 解析外观实例
     */
    protected static function resolveFacadeInstance($strName) {
        //已解析过，直接返回
        if (isset(static::$resolvedInstance[$strName]) && !static::isReMake()) {
            return static::$resolvedInstance[$strName];
        }
        //保存实例
        return static::$resolvedInstance[$strName] = static::$objApp->make($strName);
    }

    /**
     * 设置应用实例
     */
    public static function setFacadeApplication($objApp) {
        static::$objApp = $objApp;
    }

    /**
     * 静态魔术方法
     * @param  string  $strMethod
     * @param  array   $arrArgs
     */
    public static function __callStatic($strMethod, $arrArgs) {
        $objInstance = static::getFacadeInstance();

        if (!$objInstance) {
            throw new Exception('外观解析错误');
        }

        //执行外观实例中方法
        return $objInstance->$strMethod(...$arrArgs);
    }

}
