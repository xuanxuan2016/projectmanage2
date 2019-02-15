<?php

namespace App\Http\Controller\Web\Task;

use App\Facade\Menu;
use App\Http\Middleware\Web\CheckProject;
use App\Http\Model\Web\Task\RequireModel;
use App\Http\Middleware\Web\CheckAuthButton;
use App\Http\Controller\Web\Template\LayoutPcMainController;
use Framework\Service\Foundation\Controller as BaseController;

class RequireController extends BaseController {

    /**
     * 功能点实例
     */
    protected $objRequireModel;

    /**
     * 控制器方法对应的中间件
     * 方法名:方法对应的中间件
     */
    protected $arrMiddleware = [
        'loadList' => [[CheckAuthButton::class, 'Task.Require'], CheckProject::class],
        'loadBaseInfo' => [[CheckAuthButton::class, 'Task.Require']],
        'addRequireInfo' => [[CheckAuthButton::class, 'Task.Require.Add'], CheckProject::class],
        'loadRequireInfo' => [[CheckAuthButton::class, 'Task.Require.Edit'], CheckProject::class],
        'editRequireInfo' => [[CheckAuthButton::class, 'Task.Require.Edit'], CheckProject::class],
        'deleteRequireInfo' => [[CheckAuthButton::class, 'Task.Require.Delete'], CheckProject::class],
        'outputRequireInfo' => [[CheckAuthButton::class, 'Task.Require.Output'], CheckProject::class],
        'allotRequireInfo' => [[CheckAuthButton::class, 'Task.Require.Allot'], CheckProject::class],
        'qaRequireInfo' => [[CheckAuthButton::class, 'Task.Require.Qa'], CheckProject::class]
    ];

    /**
     * 依赖注入，使用外部类
     */
    public function __construct(RequireModel $objRequireModel) {
        $this->objRequireModel = $objRequireModel;
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
                'title' => '需求-' . Menu::getProjectName(),
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
                    ['path' => 'page/task/require.js', 'is_pack' => 1, 'is_remote' => 0]
            ],
            /**
             * css
             */
            'css' => [
                    ['path' => 'page/task/require.css', 'is_pack' => 1, 'is_remote' => 0]
            ]
        ];
    }

    /**
     * 获取页面上的按钮与弹框按钮
     */
    protected function getAuthButton() {
        return json_encode(Menu::getAuthButton('Task.Require'));
    }

    /**
     * 获取列表数据
     */
    public function loadList() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objRequireModel->loadList($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 获取弹框需要的额外数据
     */
    public function loadBaseInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objRequireModel->loadBaseInfo($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 新增需求
     */
    public function addRequireInfo() {
        $strErrMsg = '';
        $blnFlag = $this->objRequireModel->addRequireInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

    /**
     * 加载需求
     */
    public function loadRequireInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objRequireModel->loadRequireInfo($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 保存需求
     */
    public function editRequireInfo() {
        $strErrMsg = '';
        $blnFlag = $this->objRequireModel->editRequireInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

    /**
     * 删除需求
     */
    public function deleteRequireInfo() {
        $strErrMsg = '';
        $blnFlag = $this->objRequireModel->deleteRequireInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

    /**
     * 导出需求
     * todo:返回路径 or data中有attach_id标识处理
     */
    public function outputRequireInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objRequireModel->loadRequireInfo($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 分配需求
     */
    public function allotRequireInfo() {
        $strErrMsg = '';
        $blnFlag = $this->objRequireModel->allotRequireInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

    /**
     * 送测需求
     */
    public function qaRequireInfo() {
        $strErrMsg = '';
        $blnFlag = $this->objRequireModel->qaRequireInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

}
