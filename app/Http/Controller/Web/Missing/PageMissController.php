<?php

namespace App\Http\Controller\Web\Missing;

use App\Http\Controller\Web\Template\LayoutPcController;
use Framework\Service\Foundation\Controller as BaseController;

class PageMissController extends BaseController {

    /**
     * 控制器方法对应的中间件
     * 方法名:方法对应的中间件
     */
    protected $arrMiddleware = [
    ];

    /**
     * 依赖注入，使用外部类
     */
    public function __construct() {
        
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
                'title' => '页面丢失'
            ],
            /**
             * js
             * path:路径
             * is_pack:本地文件，是否需要压缩
             * is_remote:远程文件，直接加载
             * is_addhead:文件加载位置，1:head 0:body，默认0
             */
            'js' => [
                ['path' => 'page/missing/pagemiss.js', 'is_pack' => 1, 'is_remote' => 0]
            ],
            /**
             * css
             */
            'css' => [
                ['path' => 'page/missing/pagemiss.css', 'is_pack' => 1, 'is_remote' => 0]
            ]
        ];
    }

}
