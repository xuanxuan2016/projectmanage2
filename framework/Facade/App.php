<?php

namespace Framework\Facade;

use Framework\Service\Foundation\Application;

/**
 *
 * @see \Framework\Service\Foundation\Application
 */
class App extends Facade {

    /**
     * 获取外观名称
     */
    protected static function getFacadeAccessor() {
        return Application::class;
    }

}
