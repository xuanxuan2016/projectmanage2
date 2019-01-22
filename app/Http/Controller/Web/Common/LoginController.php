<?php

namespace App\Http\Controller\Web\Common;

use App\Http\Middleware\Web\LoginRedirect;
use App\Http\Model\Web\Common\LoginModel;
use App\Http\Controller\Web\Template\LayoutPcController;
use Framework\Service\Foundation\Controller as BaseController;

class LoginController extends BaseController {

    /**
     * 登录实例
     */
    protected $objLoginModel;

    /**
     * 控制器方法对应的中间件
     * 方法名:方法对应的中间件
     */
    protected $arrMiddleware = [
        'view' => [LoginRedirect::class]
    ];

    /**
     * 依赖注入，使用外部类
     */
    public function __construct(LoginModel $objLoginModel) {
        $this->objLoginModel = $objLoginModel;
    }

    /**
     * 获取视图模板里填充的数据
     * 模板,内容,js,css
     */
    protected function getViewData() {
        return [
            /**
             * 页面模板
             */
            'template' => [
                'controller' => LayoutPcController::class,
                'view' => 'web/template/layoutpc'
            ],
            /**
             * 文档内容
             */
            'content' => [
                'title' => '登录'
            ],
            /**
             * js
             * path:路径
             * is_pack:本地文件，是否需要压缩
             * is_remote:远程文件，直接加载
             * is_addhead:文件加载位置，1:head 0:body，默认0
             */
            'js' => [
                ['path' => 'page/common/login.js', 'is_pack' => 1, 'is_remote' => 0]
            ],
            /**
             * css
             */
            'css' => [
                ['path' => 'page/common/login.css', 'is_pack' => 1, 'is_remote' => 0]
            ]
        ];
    }

    /**
     * 退出
     */
    public function logOut() {
        $strErrMsg = '';
        $blnFlag = $this->objLoginModel->logOut($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

    /**
     * 登录
     */
    public function login() {
        $strErrMsg = '';
        $blnFlag = $this->objLoginModel->login($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

}
