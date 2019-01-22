<?php

namespace Framework\Service\Foundation\BootStrap;

use Framework\Service\Foundation\Application;
use Framework\Facade\Facade;
use Framework\Service\Foundation\AliasLoader;

class RegisterFacades {

    public function bootStrap(Application $objApp) {
        //将应用设置到外观中
        Facade::setFacadeApplication($objApp);

        //注册别名自动加载
        AliasLoader::getInstance($objApp->make('config')->get('app.facade'))->registerAutoLoad();
    }

}
