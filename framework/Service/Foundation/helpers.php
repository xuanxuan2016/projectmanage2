<?php

use Framework\Service\Foundation\Container;
use Framework\Service\Validation\ValidFormat;

/*
 * 全局通用方法
 */

if (!function_exists('app')) {

    /**
     * 获取容器实例
     */
    function app($strAbstract = null, array $arrParameters = []) {
        if (is_null($strAbstract)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($strAbstract, $arrParameters);
    }

}

if (!function_exists('getMicroTime')) {

    /**
     * 获取当前格式化的毫秒时间
     * <br>2018-08-08 08:08:08.688
     */
    function getMicroTime() {
        list($intSec, $intUsec ) = explode(" ", microtime());
        $strDate = date('Y-m-d H:i:s', $intUsec);
        $intSec = round($intSec * 1000);
        $intSec = $intSec >= 1000 ? 999 : $intSec;
        $intSec = str_pad($intSec, 3, '0', STR_PAD_LEFT);
        return $strDate . '.' . $intSec;
    }

}

if (!function_exists('getMicroTimeStamp')) {

    /**
     * 获取当前格式化的毫秒时间的时间戳
     */
    function getMicroTimeStamp() {
        list($intSec, $intUsec) = explode(" ", microtime());
        $intSec = round($intSec * 1000);
        $intSec = $intSec >= 1000 ? 999 : $intSec;
        return $intUsec * 1000 + $intSec;
    }

}

if (!function_exists('getGUID')) {

    /**
     * 获取guid
     */
    function getGUID() {
        mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
        $strCharId = strtoupper(md5(uniqid(rand(), true)));
        $strHyphen = chr(45); // "-"
        $strGuid = substr($strCharId, 0, 8) . $strHyphen
                . substr($strCharId, 8, 4) . $strHyphen
                . substr($strCharId, 12, 4) . $strHyphen
                . substr($strCharId, 16, 4) . $strHyphen
                . substr($strCharId, 20, 12);
        return $strGuid;
    }

}

if (!function_exists('checkFormat')) {

    /**
     * 检查数据格式是否正确
     * @param mix $mixValue 需要检查的数据
     * @param string $strFormatType 需要匹配的格式，详见Config.const.ValidFormat
     */
    function checkFormat($mixValue, $strFormatType) {
        return app(ValidFormat::class)->check($mixValue, $strFormatType);
    }

}

if (!function_exists('getStrLength')) {

    /**
     * 获取字符串的字节数
     * @param string $strValue 字符串
     * @param string $strSqlType 数据库类型
     */
    function getStrLength($strValue, $strSqlType = 'mysql') {
        if ($strSqlType == 'mysql') {
            return mb_strlen($strValue, 'UTF8');
        } else {
            return (strlen($strValue) + mb_strlen($strValue, 'UTF8')) / 2;
        }
    }

}
