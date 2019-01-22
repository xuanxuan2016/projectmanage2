<?php

namespace Framework\Facade;

/**
 * @method static string getParam($strParamName, $strDefault = '')
 * @method static array getAllParam()
 * @method static string getCookie($strCookieName)
 * @method static string getUri()
 * @method static bool isAjax()
 * @method static string getSecondDir()
 * @method static string getClientIP()
 * @method static string getServerIP()
 * @method static void setRequestID($strRequestID)
 * @method static string getRequestID()
 *
 * @see \Framework\Service\Http\HttpRequest
 * @see \Framework\Service\Http\ConsoleRequest
 */
class Request extends Facade {

    /**
     * 获取外观名称
     */
    protected static function getFacadeAccessor() {
        return 'request';
    }

}
