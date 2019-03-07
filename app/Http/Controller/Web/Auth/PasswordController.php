<?php

namespace App\Http\Controller\Web\Auth;

use App\Facade\Menu;
use App\Http\Model\Web\Auth\PasswordModel;
use App\Http\Middleware\Web\CheckAuthButton;
use App\Http\Controller\Web\Template\LayoutPcMainController;
use Framework\Service\Foundation\Controller as BaseController;

class PasswordController extends BaseController {

    /**
     * 密码实例
     */
    protected $objPasswordModel;

    /**
     * 控制器方法对应的中间件
     * 方法名:方法对应的中间件
     */
    protected $arrMiddleware = [
        'savePasswordInfo' => [[CheckAuthButton::class, 'Auth.Password.Edit']]
    ];

    /**
     * 依赖注入，使用外部类
     */
    public function __construct(PasswordModel $objPasswordModel) {
        $this->objPasswordModel = $objPasswordModel;
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
                'controller' => LayoutPcMainController::class,
                'view' => 'web/template/layoutpcmain'
            ],
            /**
             * 文档内容
             */
            'content' => [
                'title' => '密码设置',
                'auth_button' => $this->getAuthButton()
            ],
            /**
             * js
             * path:路径
             * is_pack:本地文件，是否需要压缩
             * is_remote:远程文件，直接加载
             * is_addhead:文件加载位置，1:head 0:body，默认0
             */
            'js' => [
                    ['path' => 'page/auth/password.js', 'is_pack' => 1, 'is_remote' => 0]
            ],
            /**
             * css
             */
            'css' => [
                    ['path' => 'page/auth/password.css', 'is_pack' => 1, 'is_remote' => 0]
            ]
        ];
    }

    /**
     * 获取页面上的按钮与弹框按钮
     */
    protected function getAuthButton() {
        return json_encode(Menu::getAuthButton('Auth.Password'));
    }

    /**
     * 保存密码信息
     */
    public function savePasswordInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objPasswordModel->savePasswordInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

}
