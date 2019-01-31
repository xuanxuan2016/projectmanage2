<?php

namespace App\Http\Controller\Web\Project;

use App\Facade\Menu;
use App\Http\Middleware\Web\CheckProject;
use App\Http\Model\Web\Project\ModuleModel;
use App\Http\Middleware\Web\CheckAuthButton;
use App\Http\Controller\Web\Template\LayoutPcMainController;
use Framework\Service\Foundation\Controller as BaseController;

class ModuleController extends BaseController {

    /**
     * 功能点实例
     */
    protected $objModuleModel;

    /**
     * 控制器方法对应的中间件
     * 方法名:方法对应的中间件
     */
    protected $arrMiddleware = [
        'loadList' => [[CheckAuthButton::class, 'Project.Module'], CheckProject::class],
        'loadBaseInfo' => [[CheckAuthButton::class, 'Project.Module']],
        'loadModuleInfo' => [[CheckAuthButton::class, 'Project.Module.Edit'], CheckProject::class],
        'saveModuleInfo' => [[CheckAuthButton::class, 'Project.Module.Edit'], CheckProject::class],
        'deleteModuleInfo' => [[CheckAuthButton::class, 'Project.Module.Edit'], CheckProject::class]
    ];

    /**
     * 依赖注入，使用外部类
     */
    public function __construct(ModuleModel $objModuleModel) {
        $this->objModuleModel = $objModuleModel;
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
                'title' => '模块设置',
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
                ['path' => 'page/project/module.js', 'is_pack' => 1, 'is_remote' => 0]
            ],
            /**
             * css
             */
            'css' => [
                ['path' => 'page/project/module.css', 'is_pack' => 1, 'is_remote' => 0]
            ]
        ];
    }

    /**
     * 获取页面上的按钮与弹框按钮
     */
    protected function getAuthButton() {
        return json_encode(Menu::getAuthButton('Project.Module'));
    }

    /**
     * 获取列表数据
     */
    public function loadList() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objModuleModel->loadList($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 获取弹框需要的额外数据
     */
    public function loadBaseInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objModuleModel->loadBaseInfo($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 加载模块信息
     */
    public function loadModuleInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objModuleModel->loadModuleInfo($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 保存模块信息
     */
    public function saveModuleInfo() {
        $strErrMsg = '';
        $blnFlag = $this->objModuleModel->saveModuleInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

    /**
     * 删除模块信息
     */
    public function deleteModuleInfo() {
        $strErrMsg = '';
        $blnFlag = $this->objModuleModel->deleteModuleInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

}
