<?php

namespace App\Http\Controller\Web\Task;

use App\Facade\Menu;
use Framework\Facade\User;
use App\Http\Model\Web\Task\TodoModel;
use App\Http\Middleware\Web\CheckAuthButton;
use App\Http\Controller\Web\Template\LayoutPcMainController;
use Framework\Service\Foundation\Controller as BaseController;

class TodoController extends BaseController {

    /**
     * 功能点实例
     */
    protected $objTodoModel;

    /**
     * 控制器方法对应的中间件
     * 方法名:方法对应的中间件
     */
    protected $arrMiddleware = [
        'loadList' => [[CheckAuthButton::class, 'Task.ToDo']],
        'loadBaseInfo' => [[CheckAuthButton::class, 'Task.ToDo']],
        'editTodoInfo' => [[CheckAuthButton::class, 'Task.ToDo.Edit']],
        'deleteTodoInfo' => [[CheckAuthButton::class, 'Task.ToDo.Edit']],
        'doneTodoInfo' => [[CheckAuthButton::class, 'Task.ToDo.Edit']]
    ];

    /**
     * 依赖注入，使用外部类
     */
    public function __construct(TodoModel $objTodoModel) {
        $this->objTodoModel = $objTodoModel;
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
                'title' => '待办事项',
                'auth_button' => $this->getAuthButton(),
                'auth_role' => User::getAccountRoleName()
            ],
            /**
             * js
             * path:路径
             * is_pack:本地文件，是否需要压缩
             * is_remote:远程文件，直接加载
             * is_addhead:文件加载位置，1:head 0:body，默认0
             */
            'js' => [
                    ['path' => 'page/task/todo.js', 'is_pack' => 1, 'is_remote' => 0]
            ],
            /**
             * css
             */
            'css' => [
                    ['path' => 'page/task/todo.css', 'is_pack' => 1, 'is_remote' => 0]
            ]
        ];
    }

    /**
     * 获取页面上的按钮与弹框按钮
     */
    protected function getAuthButton() {
        return json_encode(Menu::getAuthButton('Task.ToDo'));
    }

    /**
     * 获取列表数据
     */
    public function loadList() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objTodoModel->loadList($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 获取弹框需要的额外数据
     */
    public function loadBaseInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objTodoModel->loadBaseInfo($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 加载事项
     */
    public function loadTodoInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objTodoModel->loadTodoInfo($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 编辑事项
     */
    public function editTodoInfo() {
        $strErrMsg = '';
        $blnFlag = $this->objTodoModel->editTodoInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

    /**
     * 删除事项
     */
    public function deleteTodoInfo() {
        $strErrMsg = '';
        $blnFlag = $this->objTodoModel->deleteTodoInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

    /**
     * 完成事项
     */
    public function doneTodoInfo() {
        $strErrMsg = '';
        $blnFlag = $this->objTodoModel->doneTodoInfo($strErrMsg);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg];
    }

}
