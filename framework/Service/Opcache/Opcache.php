<?php

namespace Framework\Service\Opcache;

class Opcache {

    /**
     * 重置所有缓存
     */
    public function reset() {
        return opcache_reset();
    }

    /**
     * 检查脚本是否在缓存中
     * @param string $strScriptPath 脚本的绝对路径地址
     * 如：/vagrant/htdocs/Interview2/framework/Service/Foundation/Application.php
     * @return bool
     */
    public function hasCache($strScriptPath) {
        return opcache_is_script_cached($strScriptPath);
    }

    /**
     * 设置脚本缓存
     * @param string $strScriptPath 脚本的绝对路径地址
     */
    public function setCache($strScriptPath) {
        return opcache_compile_file($strScriptPath);
    }

    /**
     * 删除脚本缓存
     * 此方法不会直接从缓存中清除脚本，只是会对脚本重新进行编译
     * @param string $strScriptPath 脚本的绝对路径地址
     */
    public function delCache($strScriptPath) {
        return opcache_invalidate($strScriptPath, true);
    }

    /**
     * 获取缓存的配置信息
     */
    public function getConfig() {
        return opcache_get_configuration();
    }

    /**
     * 获取当前的缓存状态信息
     */
    public function getStatus() {
        return opcache_get_status(false);
    }

}
