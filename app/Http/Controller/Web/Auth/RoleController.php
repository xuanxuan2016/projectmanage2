<?php

namespace App\Http\Controller\Web\Auth;

use App\Facade\Menu;
use App\Http\Model\Web\Auth\RoleModel;
use App\Http\Middleware\Web\CheckAuthButton;
use App\Http\Controller\Web\Template\LayoutPcMainController;
use Framework\Service\Foundation\Controller as BaseController;

class RoleController extends BaseController {

    /**
     * 功能点实例
     */
    protected $objRoleModel;

    /**
     * 控制器方法对应的中间件
     * 方法名:方法对应的中间件
     */
    protected $arrMiddleware = [
        'loadList' => [[CheckAuthButton::class, 'Auth.Role']],
        'loadBaseInfo' => [[CheckAuthButton::class, 'Auth.Role']],
        'loadRoleInfo' => [[CheckAuthButton::class, 'Auth.Role.Edit']],
        'saveRoleInfo' => [[CheckAuthButton::class, 'Auth.Role.Edit']]
    ];

    /**
     * 依赖注入，使用外部类
     */
    public function __construct(RoleModel $objRoleModel) {
        $this->objRoleModel = $objRoleModel;
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
                'title' => '角色设置',
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
                ['path' => 'page/auth/role.js', 'is_pack' => 1, 'is_remote' => 0]
            ],
            /**
             * css
             */
            'css' => [
                ['path' => 'page/auth/role.css', 'is_pack' => 1, 'is_remote' => 0]
            ]
        ];
    }

    /**
     * 获取页面上的按钮与弹框按钮
     */
    protected function getAuthButton() {
        return json_encode(Menu::getAuthButton('Auth.Role'));
    }

    /**
     * 获取列表数据
     */
    public function loadList() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objRoleModel->loadList($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 获取弹框需要的额外数据
     */
    public function loadBaseInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objRoleModel->loadBaseInfo($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 加载账号信息
     */
    public function loadRoleInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objRoleModel->loadRoleInfo($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 保存账号信息
     */
    public function saveRoleInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objRoleModel->saveRoleInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

}
