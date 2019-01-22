<?php

namespace App\Facade;

use Framework\Facade\Facade;

/**
 * @method static void log($strLogType, $strKey, $arrParam = [])
 *
 * @see \App\Service\Log\LogEvent
 */
class Menu extends Facade {

    /**
     * 获取外观名称
     */
    protected static function getFacadeAccessor() {
        return 'menu';
    }

}
