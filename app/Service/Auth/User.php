<?php

namespace App\Service\Auth;

use App\Contract\Auth\User as UserContract;

/**
 * 空白用户
 */
class User implements UserContract {

    /**
     * 用户信息
     */
    protected $arrUserInfo = [];

    /**
     * 创建用户实例
     */
    public function __construct($arrUserInfo = []) {
        $this->arrUserInfo = $arrUserInfo;
    }

    /**
     * 检查用户信息完整性
     */
    public function check() {
        return false;
    }

    /**
     * 获取account_id
     */
    public function getAccountId() {
        return isset($this->arrUserInfo['account_id']) ? $this->arrUserInfo['account_id'] : '';
    }

    /**
     * 获取所有信息
     */
    public function getAllInfo() {
        return $this->arrUserInfo;
    }

}
