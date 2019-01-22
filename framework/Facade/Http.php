<?php

namespace Framework\Facade;

/**
 * @method static boolean|object curl($strUrl, $mixPost = null, $intTimeout = 10, $arrHttpHeader = [], $strProxy = '')
 * @method static boolean|string fileGetContents($strFilePath, $intTimeout = 10, $intTryCount = 1)
 *
 * @see \Framework\Service\Network\Http
 */
class Http extends Facade {

    /**
     * 获取外观名称
     */
    protected static function getFacadeAccessor() {
        return 'http';
    }

}
