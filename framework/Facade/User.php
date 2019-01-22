<?php

namespace Framework\Facade;

use App\Contract\Auth\User as UserContract;

/**
 * @method static bool check()
 * @method static string getUserId()
 * @method static string getUserName()
 *
 * @see \Framework\Service\Auth\User
 */
class User extends Facade {

    /**
     * 是否需要重新解析实例
     */
    public static function isReMake() {
        return true;
    }

    /**
     * 获取外观名称
     */
    protected static function getFacadeAccessor() {
        return UserContract::class;
    }

}
