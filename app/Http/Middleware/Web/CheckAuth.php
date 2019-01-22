<?php

namespace App\Http\Middleware\Web;

use Closure;
use Framework\Facade\Config;
use App\Exception\AuthException;
use Framework\Contract\Http\Request;
use App\Contract\Auth\User as UserContract;
use Framework\Service\Foundation\Application;

/**
 * 用户检查
 */
class CheckAuth {

    /**
     * 应用实例
     */
    protected $objApp;

    /**
     * 不需要检查的uri规则
     * 1.login页面
     * 2.test页面
     */
    protected $arrNotCheckPattern = [
        '/^(.*)(\/)?login(\/[a-z]+)*$/i',
        '/^(.*)(\/)?web\/test(\/[a-z]+)*$/i',
        '/^(.*)(\/)?web\/opcache(\/[a-z]+)*$/i',
        '/^(.*)(\/)?web\/missing(\/[a-z]+)*$/i'
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
        if (!$this->checkAuth($objRequest) && $this->needCheck($objRequest)) {
            throw new AuthException(json_encode(['err_msg' => '账号错误', 'redirect_url' => $this->getRedirectUrl($objRequest)]));
        }
        //运行下一个中间件
        return $mixNext($objRequest);
    }

    /**
     * uri是否需要进行检查
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
     * 用户检查
     */
    protected function checkAuth($objRequest) {
        $strCookieName = "DevLoginInfo";
        $strCookie = $objRequest->getCookie($strCookieName);
        //cookie为空
        if (empty($strCookie)) {
            return false;
        }
        //解析错误
        $arrCookie = json_decode($strCookie, true);
        if (!is_array($arrCookie)) {
            return false;
        }
        //生成用户信息
        $strClassName = 'App\Service\Auth\\UserDev';
        $objUser = new $strClassName($arrCookie);
        //检查用户信息
        if (!$objUser->check()) {
            return false;
        }
        //记录用户信息到容器
        $this->objApp->instance(UserContract::class, $objUser);
        return true;
    }

    /**
     * 获取跳转url
     */
    protected function getRedirectUrl($objRequest) {
        $strUrl = Config::get('web.domain.pc') . "web/common/login";
        $strRedirect = urlencode(trim($_SERVER['SCRIPT_URI'] . '?' . $_SERVER['QUERY_STRING'], '?'));
        return $strUrl . '?redirect=' . $strRedirect;
    }

    /**
     * 重置session
     */
    protected function resetSession() {
        session_regenerate_id(true);
        session_destroy();
    }

}
