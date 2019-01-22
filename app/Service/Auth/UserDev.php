<?php

namespace App\Service\Auth;

use App\Contract\Auth\User as UserContract;

/**
 * 用户
 */
class UserDev implements UserContract {

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
        if ((isset($this->arrUserInfo['account_id']) && !empty($this->arrUserInfo['account_id']))) {
            return true;
        }
        return false;
    }

    /**
     * 获取account_id
     */
    public function getAccountId() {
        return isset($this->arrUserInfo['account_id']) ? $this->arrUserInfo['account_id'] : '';
    }

    /**
     * 获取account_name
     */
    public function getAccountName() {
        return isset($this->arrUserInfo['account_name']) ? $this->arrUserInfo['account_name'] : '';
    }

    /**
     * 获取account_role
     */
    public function getAccountRole() {
        return isset($this->arrUserInfo['account_role']) ? $this->arrUserInfo['account_role'] : '';
    }

    /**
     * 获取所有信息
     */
    public function getAllInfo() {
        return $this->arrUserInfo;
    }

}
