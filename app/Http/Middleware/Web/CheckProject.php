<?php

namespace App\Http\Middleware\Web;

use Closure;
use Framework\Facade\Config;
use Framework\Contract\Http\Request;
use App\Exception\UserAgentException;
use Framework\Service\Foundation\Application;

/**
 * 项目检查
 * 1.用户是否有项目权限
 */
class CheckProject {

    /**
     * 应用实例
     */
    protected $objApp;

    /**
     * 需要检查的3级目录
     */
    protected $arrCheckPattern = ['interview', 'jobseek'];

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
            if (!$objRequest->isAjax() && $objRequest->isMobile()) {
                throw new UserAgentException(json_encode(['err_msg' => '重定向到手机版', 'redirect_url' => $this->getRedirectUrl($objRequest)]));
            }
        }
        //运行下一个中间件
        return $mixNext($objRequest);
    }

    /**
     * uri是否需要进行检查
     */
    protected function needCheck($objRequest) {
        return in_array($objRequest->getThirdDir(), $this->arrCheckPattern);
    }

    /**
     * 获取跳转url
     */
    protected function getRedirectUrl($objRequest) {
        $arrUri = explode('/', $objRequest->getUri());
        $strUrl = Config::get('web.domain.mobile') . "{$arrUri[1]}/{$arrUri[3]}";
        return $strUrl . '?' . $_SERVER['QUERY_STRING'];
    }

}
