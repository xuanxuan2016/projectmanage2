<?php

namespace App\Contract\Auth;

interface User {

    /**
     * 检查用户信息完整性
     */
    public function check();

    /**
     * 获取所有信息
     */
    public function getAllInfo();
}
