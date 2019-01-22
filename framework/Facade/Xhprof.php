<?php

namespace Framework\Facade;

use Framework\Service\Xhprof\Xhprof as XhprofService;

/**
 * @method static void start()
 * @method static void stop($strDir = '', $strType = '')
 *
 * @see \Framework\Service\Xhprof\Xhprof
 */
class Xhprof extends Facade {

    /**
     * 获取外观名称
     */
    protected static function getFacadeAccessor() {
        return XhprofService::class;
    }

}
