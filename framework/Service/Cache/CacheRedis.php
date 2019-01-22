<?php

namespace Framework\Service\Cache;

use Redis;
use Exception;
use Framework\Facade\Log;
use Framework\Facade\Config;
use Framework\Contract\Cache\Cache as CacheContract;

/**
 * 缓存的redis实现
 * https://github.com/phpredis/phpredis
 */
class CacheRedis implements CacheContract {

    /**
     * 复用redis连接类
     */
    use RedisConnect;

    /**
     * 执行redis命令
     * @param string $strCommand 命令类型
     * @param mix $mixParam 命令参数
     * @param bool $blnTry 当前命令是否为重试执行
     * @param string $strTryReason 重试原因
     * @return string|array|int|bool 异常返回false，其它情况返回命令的执行结果
     */
    public function exec($strCommand, $mixParam, $blnTry = false, $strTryReason = '') {
        try {
            $dateStartTime = $dateEndTime = getMicroTime();
            //是否存在有效命令
            if (!method_exists($this, $strCommand)) {
                throw new Exception("{$strCommand}命令不存在");
            }
            //执行命令
            return $this->$strCommand($mixParam, $blnTry);
        } catch (Exception $e) {
            //异常重试一次
            if ($blnTry == false) {
                return $this->exec($strCommand, $mixParam, true, $e->getMessage());
            }
            //异常日志记录
            $dateEndTime = getMicroTime();
            $strLog = sprintf("\n command:%s \n param:%s \n istry:%s \n exception:%s \n startdate:%s \n enddate:%s \n connectinfo:%s \n", $strCommand, json_encode($mixParam), $blnTry, $e->getMessage(), $dateStartTime, $dateEndTime, json_encode($this->arrCurConnectInfo));
            Log::log($strLog, Config::get('const.Log.LOG_REDISERR'));
            return false;
        } finally {
            $dateEndTime = getMicroTime();
            //重试日志记录
            if ($blnTry) {
                $strLog = sprintf("\n command:%s \n param:%s \n istry:%s \n exception:%s \n startdate:%s \n enddate:%s \n connectinfo:%s \n", $strCommand, json_encode($mixParam), $blnTry, $strTryReason, $dateStartTime, $dateEndTime, json_encode($this->arrCurConnectInfo));
                Log::log($strLog, Config::get('const.Log.LOG_REDISTYR'));
            }
            if (Config::get('redis.log_info')) {
                $strLog = sprintf("\n command:%s \n param:%s \n startdate:%s \n enddate:%s \n", $strCommand, json_encode($mixParam), $dateStartTime, $dateEndTime);
                Log::log($strLog, Config::get('const.Log.LOG_REDISINFO'));
            }
        }
    }

    /**
     * 判断键是否存在
     * @param mix $mixParam 参数，如key1，['key1','key2']
     * @return int|bool
     */
    public function exists($mixParam, $blnTry = false) {
        $objReadHander = $this->getReadHander($blnTry);
        return is_null($objReadHander) ? false : $objReadHander->exists($mixParam);
    }

    /**
     * 获取string值
     * @param mix $mixParam 参数，如key1，['key1','key2']
     * @return string|array|bool
     */
    public function get($mixParam, $blnTry = false) {
        $objReadHander = $this->getReadHander($blnTry);
        return is_null($objReadHander) ? false : (is_array($mixParam) ? $objReadHander->mGet($mixParam) : $objReadHander->get($mixParam));
    }

    /**
     * 获取hash值
     * @param type $mixParam
     * @return array|bool
     */
    public function hGetAll($mixParam, $blnTry = false) {
        $objReadHander = $this->getReadHander($blnTry);
        if (is_null($objReadHander)) {
            return false;
        }
        //处理key为数组
        $arrKey = $mixParam['key'];
        if (!is_array($arrKey)) {
            $arrKey = [$arrKey];
        }
        //管道处理
        $objReadHander->multi(Redis::PIPELINE);
        foreach ($arrKey as $strKey) {
            $objReadHander->hGetAll($strKey);
        }
        return $objReadHander->exec();
    }

    /**
     * 获取hash值
     * @param mix $mixParam 参数，如['key'=>['key1','key2'],'field'=>['field1','field2']]
     * @return array|bool
     */
    public function hMGet($mixParam, $blnTry = false) {
        $objReadHander = $this->getReadHander($blnTry);
        if (is_null($objReadHander)) {
            return false;
        }
        //处理key为数组
        $arrKey = $mixParam['key'];
        if (!is_array($arrKey)) {
            $arrKey = [$arrKey];
        }
        //管道处理
        $objReadHander->multi(Redis::PIPELINE);
        foreach ($arrKey as $strKey) {
            $objReadHander->hMGet($strKey, $mixParam['field']);
        }
        return $objReadHander->exec();
    }

    /**
     * 获取list值
     * @param mix $mixParam 参数，如['key'=>'aa','start'=>0,'end'=>2]
     * @return array|bool
     */
    public function lRange($mixParam, $blnTry = false) {
        $objReadHander = $this->getReadHander($blnTry);
        return is_null($objReadHander) ? false : $objReadHander->lRange($mixParam['key'], $mixParam['start'], $mixParam['end']);
    }

    /**
     * 获取set中元素个数
     * @param mix $mixParam 参数，如key1
     * @return int|bool
     */
    public function sCard($mixParam, $blnTry = false) {
        $objReadHander = $this->getReadHander($blnTry);
        return is_null($objReadHander) ? false : $objReadHander->sCard($mixParam);
    }

    /**
     * 判断元素是否属于某个set
     * @param mix $mixParam 参数，如['key'=>'aa','value'=>'1']
     * @return int|bool
     */
    public function sIsMember($mixParam, $blnTry = false) {
        $objReadHander = $this->getReadHander($blnTry);
        return is_null($objReadHander) ? false : $objReadHander->sismember($mixParam['key'], $mixParam['value']);
    }

    /**
     * 设置string值
     * @param string $mixParam 参数，如['key'=>'key1','value'=>'value1','expire'=>0]
     * @return bool
     */
    public function set($mixParam, $blnTry = false) {
        $objWriteHander = $this->getWriteHander($blnTry);
        if (is_null($objWriteHander)) {
            return false;
        }
        return isset($mixParam['expire']) && $mixParam['expire'] > 0 ? $objWriteHander->set($mixParam['key'], $mixParam['value'], $mixParam['expire']) : $objWriteHander->set($mixParam['key'], $mixParam['value']);
    }

    /**
     * 设置hash值
     * @param mix $mixParam 参数，如[['key' => 'key1', 'value' => ['a' => 'value1', 'b' => 'value2'], 'expire' => 0]]
     * @return bool
     */
    public function hMSet($mixParam, $blnTry = false) {
        $objWriteHander = $this->getWriteHander($blnTry);
        if (is_null($objWriteHander)) {
            return false;
        }
        //管道处理
        $objWriteHander->multi(Redis::PIPELINE);
        foreach ($mixParam as $arrTmp) {
            $objWriteHander->hMSet($arrTmp['key'], $arrTmp['value']);
            if (isset($arrTmp['expire']) && $arrTmp['expire'] > 0) {
                $objWriteHander->expire($arrTmp['key'], $arrTmp['expire']);
            }
        }
        $arrRes = $objWriteHander->exec();
        //报告错误
        foreach ($arrRes as $mixValue) {
            if ($mixValue === false) {
                Log::log('hMSet存在失败:' . json_encode($mixParam), Config::get('const.Log.LOG_REDISERR'));
                break;
            }
        }
        return true;
    }

    /**
     * 设置list值
     * @param mix $mixParam 参数，如['key' => 'key1', 'value' => ['value1','value2'], 'expire' => 0]
     * @return bool|int
     */
    public function lPush($mixParam, $blnTry = false) {
        $objWriteHander = $this->getWriteHander($blnTry);
        if (is_null($objWriteHander)) {
            return false;
        }
        //管道处理
        $objWriteHander->multi(Redis::PIPELINE);
        foreach ($mixParam['value'] as $strValue) {
            $objWriteHander->lPush($mixParam['key'], $strValue);
        }
        $blnExpire = false;
        if (isset($mixParam['expire']) && $mixParam['expire'] > 0) {
            $blnExpire = true;
            $objWriteHander->expire($mixParam['key'], $mixParam['expire']);
        }
        $arrRes = $objWriteHander->exec();
        //报告错误
        foreach ($arrRes as $mixValue) {
            if ($mixValue === false) {
                Log::log('lpush存在失败:' . json_encode($mixParam), Config::get('const.Log.LOG_REDISERR'));
                break;
            }
        }
        return !$blnExpire ? $arrRes[count($arrRes) - 1] : $arrRes[count($arrRes) - 2];
    }

    /**
     * 设置list值，当key存在时
     * @param mix $mixParam 参数，如['key' => 'key1', 'value' => ['value1','value2'], 'expire' => 0]
     * @return bool|int
     */
    public function lPushx($mixParam, $blnTry = false) {
        $objWriteHander = $this->getWriteHander($blnTry);
        if (is_null($objWriteHander)) {
            return false;
        }
        //管道处理
        $objWriteHander->multi(Redis::PIPELINE);
        foreach ($mixParam['value'] as $strValue) {
            $objWriteHander->lPushx($mixParam['key'], $strValue);
        }
        $blnExpire = false;
        if (isset($mixParam['expire']) && $mixParam['expire'] > 0) {
            $blnExpire = true;
            $objWriteHander->expire($mixParam['key'], $mixParam['expire']);
        }
        $arrRes = $objWriteHander->exec();
        //报告错误
        foreach ($arrRes as $mixValue) {
            if ($mixValue === false) {
                Log::log('lpushx存在失败:' . json_encode($mixParam), Config::get('const.Log.LOG_REDISERR'));
                break;
            }
        }
        return !$blnExpire ? $arrRes[count($arrRes) - 1] : $arrRes[count($arrRes) - 2];
    }

    /**
     * 截断list
     * @param mix $mixParam 参数，如['key'=>'aa','start'=>0,'end'=>2]
     * @return array|bool
     */
    public function lTrim($mixParam, $blnTry = false) {
        $objWriteHander = $this->getWriteHander($blnTry);
        return is_null($objWriteHander) ? false : $objWriteHander->lTrim($mixParam['key'], $mixParam['start'], $mixParam['end']);
    }

    /**
     * 删除key
     * @param mix $mixParam 参数，如key1，['key1','key2']
     * @return int|bool
     */
    public function del($mixParam, $blnTry = false) {
        $objWriteHander = $this->getWriteHander($blnTry);
        return is_null($objWriteHander) ? false : $objWriteHander->unlink($mixParam);
    }

    /**
     * 设置key的过期时间
     * @param mix $mixParam 参数，如['key'=>'key1','expire'=>0]
     * @return int|bool
     */
    public function expire($mixParam, $blnTry = false) {
        $objWriteHander = $this->getWriteHander($blnTry);
        return is_null($objWriteHander) ? false : $objWriteHander->expire($mixParam['key'], $mixParam['expire']);
    }

    /**
     * 获取redis的session路径
     */
    public function getSessionPath($mixParam, $blnTry = false) {
        $objWriteHander = $this->getWriteHander($blnTry);
        return is_null($objWriteHander) ? '' : $this->arrWriteHanderInfo['session_path'];
    }

    /**
     * 设置set值
     * @param mix $mixParam 参数，如['key' => 'key1', 'value' => ['value1','value2'], 'expire' => 0]
     * @return bool|int
     */
    public function sAdd($mixParam, $blnTry = false) {
        $objWriteHander = $this->getWriteHander($blnTry);
        if (is_null($objWriteHander)) {
            return false;
        }
        $intRes = $objWriteHander->sAdd($mixParam['key'], ...$mixParam['value']);
        if (isset($mixParam['expire']) && $mixParam['expire'] > 0) {
            $objWriteHander->expire($mixParam['key'], $mixParam['expire']);
        }
        return $intRes;
    }

}
