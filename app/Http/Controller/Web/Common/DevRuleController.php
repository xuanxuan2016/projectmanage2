<?php

namespace App\Http\Controller\Web\Common;

use App\Http\Middleware\Web\CheckAuthButton;
use App\Http\Model\Web\Common\DevRuleModel;
use App\Http\Controller\Web\Template\LayoutPcMainController;
use Framework\Service\Foundation\Controller as BaseController;

class DevRuleController extends BaseController {

    /**
     * 功能点实例
     */
    protected $objDevRuleModel;

    /**
     * 控制器方法对应的中间件
     * 方法名:方法对应的中间件
     */
    protected $arrMiddleware = [
        'loadlist' => [[CheckAuthButton::class, 'Home.DevRule']]
    ];

    /**
     * 依赖注入，使用外部类
     */
    public function __construct(DevRuleModel $objDevRuleModel) {
        $this->objDevRuleModel = $objDevRuleModel;
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
                'title' => '开发规则'
            ],
            /**
             * js
             * path:路径
             * is_pack:本地文件，是否需要压缩
             * is_remote:远程文件，直接加载
             * is_addhead:文件加载位置，1:head 0:body，默认0
             */
            'js' => [
                    ['path' => 'page/common/devrule.js', 'is_pack' => 1, 'is_remote' => 0]
            ],
            /**
             * css
             */
            'css' => [
                    ['path' => 'page/common/devrule.css', 'is_pack' => 1, 'is_remote' => 0],
                    ['path' => 'plugin/markdown.css', 'is_pack' => 0, 'is_remote' => 0]
            ]
        ];
    }

    /**
     * 获取列表数据
     */
    public function loadList() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objDevRuleModel->loadList($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

    /**
     * 加载规则
     */
    public function loadDevRuleInfo() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objDevRuleModel->loadDevRuleInfo($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

}
