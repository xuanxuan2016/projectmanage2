<?php

namespace App\Http\Controller\Web\Task;

use App\Facade\Menu;
use App\Http\Model\Web\Task\OnlineErrorModel;
use App\Http\Middleware\Web\CheckAuthButton;
use App\Http\Controller\Web\Template\LayoutPcMainController;
use Framework\Service\Foundation\Controller as BaseController;

class OnlineErrorController extends BaseController {

    /**
     * 功能点实例
     */
    protected $objOnlineErrorModel;

    /**
     * 控制器方法对应的中间件
     * 方法名:方法对应的中间件
     */
    protected $arrMiddleware = [
        'loadList' => [[CheckAuthButton::class, 'Task.OnlineError']],
        'loadBaseInfo' => [[CheckAuthButton::class, 'Task.OnlineError']],
        'loadOnlineErrorInfo' => [[CheckAuthButton::class, 'Task.OnlineError.View']],
        'editOnlineErrorInfo' => [[CheckAuthButton::class, 'Task.OnlineError.Edit']]
    ];

    /**
     * 依赖注入，使用外部类
     */
    public function __construct(OnlineErrorModel $objOnlineErrorModel) {
        $this->objOnlineErrorModel = $objOnlineErrorModel;
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
                'title' => '上线问题',
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
                    ['path' => 'plugin/ckeditor/ckeditor.js', 'is_pack' => 0, 'is_remote' => 0, 'is_addhead' => 1],
                    ['path' => 'page/task/onlineerror.js', 'is_pack' => 1, 'is_remote' => 0]
            ],
            /**
             * css
             */
            'css' => [
                    ['path' => 'page/task/onlineerror.css', 'is_pack' => 1, 'is_remote' => 0]
            ]
        ];
    }

    /**
     * 获取页面上的按钮与弹框按钮
     */
    protected function getAuthButton() {
        return json_encode(Menu::getAuthButton('Task.OnlineError'));
    }

    /**
     * 获取列表数据
     */
    public function loadList() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objOnlineErrorModel->loadList($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 获取弹框需要的额外数据
     */
    public function loadBaseInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objOnlineErrorModel->loadBaseInfo($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 加载上线问题
     */
    public function loadOnlineErrorInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objOnlineErrorModel->loadOnlineErrorInfo($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 保存上线问题
     */
    public function editOnlineErrorInfo() {
        $strErrMsg = '';
        $blnFlag = $this->objOnlineErrorModel->editOnlineErrorInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

}
