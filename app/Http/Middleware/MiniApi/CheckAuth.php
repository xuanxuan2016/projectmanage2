<?php

namespace App\Http\Middleware\MiniApi;

use Closure;
use Exception;
use Framework\Contract\Http\Request;
use App\Exception\AuthMiniException;
use App\Contract\Auth\User as UserContract;
use Framework\Service\Foundation\Application;

/**
 * 用户检查(小程序)
 * err_code
 * 1001:微信未登录，重新登录
 * 1002:用户未登录，自动跳转到登录页
 * 1003:用户未登录，显示登录引导框
 */
class CheckAuth {

    /**
     * 应用实例
     */
    protected $objApp;

    /**
     * 登录token实例
     */
    protected $objLoginToken;

    /**
     * 不需要检查微信登录与用户登录的uri规则
     * 1.微信登录
     */
    protected $arrNotCheckPattern = [
    ];

    /**
     * 不需要检查用户登录的uri规则
     * 1.用户登录
     * 2.分享页面
     */
    protected $arrNotCheckPattern2 = [
    ];

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
        if ($this->needCheck($objRequest)) {
            $blnFlag = $this->checkAuth($objRequest, $strErrCode, $strErrMsg);
            if (!$blnFlag) {
                throw new AuthMiniException(json_encode(['success' => 0, 'err_code' => $strErrCode, 'err_msg' => $strErrMsg]));
            }
        }
        //运行下一个中间件
        return $mixNext($objRequest);
    }

    /**
     * 是否非如下情况
     * 1.微信登录
     */
    protected function needCheck($objRequest) {
        $strUri = $objRequest->getUri();
        foreach ($this->arrNotCheckPattern as $strPattern) {
            if (preg_match($strPattern, $strUri)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 是否非如下情况
     * 1.用户登录
     * 2.分享页面
     */
    protected function needCheck2($objRequest) {
        $strUri = $objRequest->getUri();
        foreach ($this->arrNotCheckPattern2 as $strPattern) {
            if (preg_match($strPattern, $strUri)) {
                return false;
            }
        }
        return true;
    }

    /**
     * cookie检查
     * 
     * err_code说明
     * 1001:微信未登录，触发微信登录
     * 1002:未用户登录，跳转到登录页
     */
    protected function checkAuth($objRequest, &$strErrCode, &$strErrMsg) {
        try {
            //1.获取cookie
            $arrCookie = $this->getCookie($objRequest);

            //2.获取用户信息
            $objUser = $this->getUserInfo($objRequest, $arrCookie);

            //3.检查微信登录
            $this->checkWxLogin($objUser);

            //4.检查用户登录
            $this->checkUserLogin($objRequest, $objUser);

            //5.记录用户信息到容器
            $this->objApp->instance(UserContract::class, $objUser);

            //6.返回成功
            return true;
        } catch (Exception $e) {
            $strErrCode = $e->getCode();
            $strErrMsg = $e->getMessage();
            //返回失败
            return false;
        }
    }

    /**
     * 获取cookie
     */
    protected function getCookie($objRequest) {
        $strRole = $objRequest->getThirdDir();
        $strCookieName = "{$strRole}|LoginInfo";
        $strCookie = $objRequest->getCookie($strCookieName);
        //cookie为空
        if (empty($strCookie)) {
            throw new Exception('微信未登录，请重试', '1001');
        }
        //解析错误
        $arrCookie = json_decode($strCookie, true);
        if (!is_array($arrCookie)) {
            throw new Exception('微信未登录，请重试', '1001');
        }
        //返回
        return $arrCookie;
    }

    /**
     * 获取用户信息
     */
    protected function getUserInfo($objRequest, $arrCookie) {
        $strRole = $objRequest->getThirdDir();
        $strClassName = 'App\Service\Auth\\UserMini' . ucfirst($strRole);
        $objUser = new $strClassName($arrCookie);
        return $objUser;
    }

    /**
     * 检查微信登录
     */
    protected function checkWxLogin($objUser) {
        if (!$objUser->checkMini()) {
            throw new Exception('微信未登录，请重试', '1001');
        }
    }

    /**
     * 检查用户登录
     */
    protected function checkUserLogin($objRequest, $objUser) {
        if ($this->needCheck2($objRequest)) {
            //检查用户信息
            if (!$objUser->check()) {
                throw new Exception('用户未登录', '1002');
            }
        }
    }

}
