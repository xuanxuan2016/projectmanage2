<?php

namespace Framework\Contract\Cache;

/**
 * 缓存接口
 */
interface Cache {

    /**
     * 执行redis命令
     * @param string $strCommand 命令类型
     * @param mix $mixParam 命令参数
     * @param bool $blnTry 当前命令是否为重试执行
     * @param string $strTryReason 重试原因
     * @return string|array|int|bool
     */
    public function exec($strCommand, $mixParam, $blnTry = false, $strTryReason = '');
}
