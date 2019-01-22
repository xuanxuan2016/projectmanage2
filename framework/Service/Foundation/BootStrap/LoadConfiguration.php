<?php

namespace Framework\Service\Foundation\BootStrap;

use Framework\Service\Foundation\Application;
use Framework\Service\Config\Config;

class LoadConfiguration {

    /**
     * 应用实例
     */
    protected $objApp;

    public function bootStrap(Application $objApp) {
        $this->objApp = $objApp;

        $objApp->instance('config', $objConfig = new Config);

        $this->setConfigFile($objConfig);
    }

    /**
     * 设置config中的
     */
    protected function setConfigFile($objConfig) {
        $strConfigPath = $this->objApp->make('path.config');
        foreach (scandir($strConfigPath) as $strFile) {
            if (strpos($strFile, '.php')) {
                $objConfig->setFile(basename($strFile, '.php'), $strConfigPath . DIRECTORY_SEPARATOR . $strFile);
            }
        }
    }

}
