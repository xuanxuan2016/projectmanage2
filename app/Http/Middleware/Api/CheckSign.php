<?php

namespace App\Http\Middleware\Api;

use Closure;
use Framework\Facade\Log;
use Framework\Facade\Des;
use Framework\Facade\Config;
use App\Exception\ApiSignException;
use Framework\Contract\Http\Request;
use Framework\Service\Foundation\Application;

/**
 * 有效性检查
 */
class CheckSign {

    /**
     * 应用实例
     */
    protected $objApp;

    /**
     * 创建实例
     */
    public function __construct(Application $objApp) {
        $this->objApp = $objApp;
    }

    /**
     * 中间件处理
     */
    public function handle(Request $objRequest, Closure $mixNext) {
        if (!$this->checkSign($objRequest)) {
            throw new ApiSignException('接口签名信息错误');
        }
        //运行下一个中间件
        return $mixNext($objRequest);
    }

    /**
     * 有效性检查
     */
    protected function checkSign($objRequest) {
        //1.请求参数是否有sign_key,from_domain,union_id
        $arrParam = $objRequest->getAllParam();
        if (!isset($arrParam['sign_key']) || empty($arrParam['sign_key'])) {
            return false;
        }
        if (!isset($arrParam['from_domain']) || empty($arrParam['from_domain'])) {
            return false;
        }
        if (!isset($arrParam['union_id']) || empty($arrParam['union_id'])) {
            return false;
        }
        if (empty(Config::get('des.md5_key.' . $arrParam['from_domain']))) {
            return false;
        }
        $strSignKey = $arrParam['sign_key'];
        $strFromDomain = $arrParam['from_domain'];
        //2.根据参数生成签名
        unset($arrParam['sign_key']);
        unset($arrParam['from_domain']);
        unset($arrParam['union_id']);
        ksort($arrParam);
        $strParam = implode('', $arrParam);

        //3.签名校验
        if ($strSignKey == md5($strParam . Config::get('des.md5_key.' . $strFromDomain))) {
            return true;
        }
        return false;
    }

}
