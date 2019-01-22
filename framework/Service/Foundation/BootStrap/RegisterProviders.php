<?php

namespace Framework\Service\Foundation\BootStrap;

use Framework\Service\Foundation\Application;

class RegisterProviders {

    public function bootStrap(Application $objApp) {
        $objApp->registerConfiguredProviders();
    }

}
