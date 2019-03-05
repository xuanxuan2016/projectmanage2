<?php

namespace App\Http\Controller\Web\Task;

use App\Facade\Menu;
use App\Http\Model\Web\Task\QaModel;
use App\Http\Middleware\Web\CheckProject;
use App\Http\Middleware\Web\CheckAuthButton;
use App\Http\Controller\Web\Template\LayoutPcMainController;
use Framework\Service\Foundation\Controller as BaseController;

class QaController extends BaseController {

    /**
     * 功能点实例
     */
    protected $objQaModel;

    /**
     * 控制器方法对应的中间件
     * 方法名:方法对应的中间件
     */
    protected $arrMiddleware = [
        'loadList' => [[CheckAuthButton::class, 'Task.Qa'], CheckProject::class],
        'loadBaseInfo' => [[CheckAuthButton::class, 'Task.Qa']],
        'qaQaInfo' => [[CheckAuthButton::class, 'Task.Qa.Qa'], CheckProject::class],
        'onlineQaInfo' => [[CheckAuthButton::class, 'Task.Qa.Online'], CheckProject::class],
        'revokeQaInfo' => [[CheckAuthButton::class, 'Task.Qa.Revoke'], CheckProject::class],
        'downQaInfo' => [[CheckAuthButton::class, 'Task.Qa.Down'], CheckProject::class]
    ];

    /**
     * 依赖注入，使用外部类
     */
    public function __construct(QaModel $objQaModel) {
        $this->objQaModel = $objQaModel;
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
                'title' => '送测-' . Menu::getProjectName(),
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
                    ['path' => 'page/task/qa.js', 'is_pack' => 1, 'is_remote' => 0]
            ],
            /**
             * css
             */
            'css' => [
                    ['path' => 'page/task/qa.css', 'is_pack' => 1, 'is_remote' => 0]
            ]
        ];
    }

    /**
     * 获取页面上的按钮与弹框按钮
     */
    protected function getAuthButton() {
        return json_encode(Menu::getAuthButton('Task.Qa'));
    }

    /**
     * 获取列表数据
     */
    public function loadList() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objQaModel->loadList($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 获取弹框需要的额外数据
     */
    public function loadBaseInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objQaModel->loadBaseInfo($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 送测
     */
    public function qaQaInfo() {
        $strErrMsg = '';
        $blnFlag = $this->objQaModel->qaQaInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

    /**
     * 上线
     */
    public function onlineQaInfo() {
        $strErrMsg = '';
        $blnFlag = $this->objQaModel->onlineQaInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

    /**
     * 撤销
     */
    public function revokeQaInfo() {
        $strErrMsg = '';
        $blnFlag = $this->objQaModel->revokeQaInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

    /**
     * 下载
     */
    public function downQaInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objQaModel->downQaInfo($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

}
