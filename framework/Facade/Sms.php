<?php

namespace Framework\Facade;

/**
 * @method static bool sendSMS($strMobile, $strContent, $strSystemType = '20')
 *
 * @see \Framework\Service\Sms\Sms
 */
class Sms extends Facade {

    /**
     * 获取外观名称
     */
    protected static function getFacadeAccessor() {
        return 'sms';
    }

}
