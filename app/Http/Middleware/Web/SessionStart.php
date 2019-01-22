<?php

namespace App\Http\Middleware\Web;

use Closure;
use Framework\Facade\Cache;
use Framework\Contract\Http\Request;
use Framework\Service\Database\Session;
use Framework\Service\Foundation\Application;

/**
 * session开启
 */
class SessionStart {

    /**
     * 应用实例
     */
    protected $objApp;

    /**
     * session实例
     */
    protected $objSession;

    /**
     * 需要检查的3级目录
     */
    protected $arrCheckPattern = ['interview', 'jobseek'];

    /**
     * 创建实例
     */
    public function __construct(Application $objApp, Session $objSession) {
        $this->objApp = $objApp;
        $this->objSession = $objSession;
    }

    /**
     * 中间件处理
     */
    public function handle(Request $objRequest, Closure $mixNext) {
        if ($this->needStart($objRequest) && !$this->isSessionStarted()) {
            $this->startSession();
        }
        //运行下一个中间件
        return $mixNext($objRequest);
    }

    /**
     * 开启session
     */
    protected function startSession() {
        $strSessionPath = Cache::exec('getSessionPath', '');
        if (!empty($strSessionPath)) {
            ini_set("session.save_handler", "redis");
            ini_set("session.save_path", $strSessionPath);
        } else {
            $this->objSession->initHandler();
        }
        session_start();
    }

    /**
     * uri是否可开启session
     */
    protected function needStart($objRequest) {
        return in_array($objRequest->getThirdDir(), $this->arrCheckPattern);
    }

    /**
     * 判断session是否开启
     * @return boolean
     */
    protected function isSessionStarted() {
        if (!$this->objApp->runningInConsole()) {
            return session_status() === PHP_SESSION_ACTIVE ? true : false;
        }
        return true;
    }

}
