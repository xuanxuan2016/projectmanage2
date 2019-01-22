<?php

namespace App\Http\Middleware\Web;

use Closure;
use Framework\Facade\User;
use Framework\Facade\Config;
use Framework\Contract\Http\Request;
use Framework\Service\Foundation\Application;

/**
 * login-view中间件
 * 1.如果用户已登录则跳转到redirect页面，否则跳转到默认页面
 */
class LoginRedirect {

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
        if (User::check()) {
            $objRequest->redirect($this->getRedirectUrl($objRequest));
        }
        //运行下一个中间件
        return $mixNext($objRequest);
    }

    /**
     * 获取跳转url
     */
    protected function getRedirectUrl($objRequest) {
        return !empty($objRequest->getParam('redirect')) ? $objRequest->getParam('redirect') : Config::get('web.redirect.uri_empty');
    }

}
