<?php

namespace Framework\Facade;

/**
 * @method static string md5($strValue, $strSalt = '')
 * @method static string passwordHash($strPassword)
 * @method static bool passwordVerify($strPassword, $strPasswordHash)
 * @method static string encrypt($strValue)
 * @method static string decrypt($strValue)
 *
 * @see \Framework\Service\Security\Des
 */
class Des extends Facade {

    /**
     * 获取外观名称
     */
    protected static function getFacadeAccessor() {
        return 'des';
    }

}
