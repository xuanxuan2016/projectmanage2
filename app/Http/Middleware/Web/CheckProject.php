<?php

namespace App\Http\Middleware\Web;

use Closure;
use App\Facade\Menu;
use Framework\Contract\Http\Request;
use App\Exception\AuthButtonException;
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
     * 创建实例
     */
    public function __construct(Application $objApp) {
        $this->objApp = $objApp;
    }

    /**
     * 中间件处理
     */
    public function handle(Request $objRequest, Closure $mixNext) {
        if (!$this->check()) {
            throw new AuthButtonException(json_encode(['success' => 0, 'err_msg' => '无项目权限']));
        }
        //运行下一个中间件
        return $mixNext($objRequest);
    }

    /**
     * 检查项目权限
     */
    protected function check() {
        return Menu::checkProject();
    }

}
