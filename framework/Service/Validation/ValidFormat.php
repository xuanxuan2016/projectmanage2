<?php

namespace Framework\Service\Validation;

use Framework\Facade\Config;

class ValidFormat {

    /**
     * int
     */
    const FORMAT_INT = 'int';

    /**
     * positive int
     */
    const FORMAT_POSINT = 'posint';

    /**
     * decimal
     */
    const FORMAT_DECIMAL = 'decimal';

    /**
     * datetime
     */
    const FORMAT_DATETIME = 'datetime';

    /**
     * guid
     */
    const FORMAT_UNIQ = 'uniq';

    /**
     * email
     */
    const FORMAT_EMAIL = 'email';

    /**
     * 身份证
     */
    const FORMAT_IDCARD = 'idcard';

    /**
     * 手机
     */
    const FORMAT_MOBILE = 'mobile';

    /**
     * 用户名：需要以字母开头，长度为6-18位，只能包含字母数字下划线
     */
    const FORMAT_USERNAME = 'username';

    /**
     * 密码：需要包含字母与数字，长度为6-18位，只能包含字母数字下划线
     */
    const FORMAT_PASSWORD = 'password';

    /**
     * 文件名：长度为1-50位，只能包含中文字母数字下划线中划线空格中文括号英文括号
     */
    const FORMAT_FILENAME = 'filename';

    /**
     * 文件名：长度为1-10位，只能包含中文字母数字
     */
    const FORMAT_VIEWNAME = 'viewname';

    /**
     * 检查数据格式是否正确
     * @param mix $mixValue 需要检查的数据
     * @param string $strFormatType 需要匹配的格式
     */
    public function check($mixValue, $strFormatType) {
        if (empty($strFormatType) || $mixValue === '') {
            return false;
        }
        switch ($strFormatType) {
            case ValidFormat::FORMAT_DECIMAL:
                $strPreg = '/^(-?\d+)(\.\d+)?$/';
                if (preg_match($strPreg, $mixValue)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case ValidFormat::FORMAT_INT:
                $strPreg = '/^-?\d+$/';
                if (preg_match($strPreg, $mixValue)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case ValidFormat::FORMAT_POSINT:
                $strPreg = '/^[1-9]\d*$/';
                if (preg_match($strPreg, $mixValue)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case ValidFormat::FORMAT_DATETIME:
                $mixDate = strtotime($mixValue) ? strtotime($mixValue) : false;
                if ($mixDate === false) {
                    return false;
                } else {
                    return true;
                }
                break;
            case ValidFormat::FORMAT_UNIQ:
                $strPreg = '/^[A-Fa-f0-9]{8}(-[A-Fa-f0-9]{4}){3}-[A-Fa-f0-9]{12}$/';
                if (preg_match($strPreg, $mixValue)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case ValidFormat::FORMAT_EMAIL:
                $strPreg = '/^[a-zA-Z0-9_\.-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/';
                if (preg_match($strPreg, $mixValue)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case ValidFormat::FORMAT_USERNAME:
                $strPreg = '/^[a-zA-Z][a-zA-Z0-9_]{5,17}$/';
                if (preg_match($strPreg, $mixValue)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case ValidFormat::FORMAT_PASSWORD:
                $strPreg = '/^(?!^\d+$)(?!^[a-zA-Z_]+$)[a-zA-Z0-9_]{6,18}$/';
                if (preg_match($strPreg, $mixValue)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case ValidFormat::FORMAT_IDCARD:
                $strPreg15 = '/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/';
                $strPreg18 = '/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/';
                if (preg_match($strPreg15, $mixValue) || preg_match($strPreg18, $mixValue)) {
                    if (!ValidFormat::checkIDCard($mixValue)) {
                        return false;
                    }
                    return true;
                } else {
                    return false;
                }
                break;
            case ValidFormat::FORMAT_MOBILE:
                $strPreg = '/^\d{11,11}$/';
                if (preg_match($strPreg, $mixValue)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case ValidFormat::FORMAT_FILENAME:
                $strPreg = '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9_\s()（）-]{1,50}$/u';
                if (preg_match($strPreg, $mixValue)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case ValidFormat::FORMAT_VIEWNAME:
                $strPreg = '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]{1,10}$/u';
                if (preg_match($strPreg, $mixValue)) {
                    return true;
                } else {
                    return false;
                }
                break;
            default :
                return false;
                break;
        }
    }

}
