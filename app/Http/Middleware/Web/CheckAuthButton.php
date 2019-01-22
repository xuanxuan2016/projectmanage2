<?php

namespace App\Http\Middleware\Web;

use Closure;
use App\Facade\Menu;
use Framework\Contract\Http\Request;
use App\Exception\AuthButtonException;
use Framework\Service\Foundation\Application;

/**
 * 按钮权限检查
 */
class CheckAuthButton {

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
    public function handle(Request $objRequest, Closure $mixNext, $strAuthCode) {
        if (!$this->check($strAuthCode)) {
            throw new AuthButtonException(json_encode(['success' => 0, 'err_msg' => '无操作权限']));
        }
        //运行下一个中间件
        return $mixNext($objRequest);
    }

    /**
     * 检查按钮权限
     */
    protected function check($strAuthCode) {
        return Menu::checkAuthButton($strAuthCode);
    }

}
