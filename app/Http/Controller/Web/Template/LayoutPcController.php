<?php

namespace App\Http\Controller\Web\Template;

use Framework\Facade\Config;
use Framework\Facade\Request;
use Framework\Service\Foundation\Controller as BaseController;

class LayoutPcController extends BaseController {

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
    public function getViewData() {
        return [
            /**
             * 文档内容
             */
            'content' => [
            ],
            /**
             * js
             * path:路径
             * is_pack:本地文件，是否需要压缩
             * is_remote:远程文件，直接加载
             * is_addhead:文件加载位置，1:head 0:body，默认0
             */
            'js' => [
                ['path' => 'plugin/axios.min.js', 'is_pack' => 0, 'is_remote' => 0, 'is_addhead' => 1],
                ['path' => Config::get('web.js.read_only') ? 'plugin/vue.min.js' : 'plugin/vue.js', 'is_pack' => 0, 'is_remote' => 0, 'is_addhead' => 1],
                ['path' => 'plugin/elementui.js', 'is_pack' => 0, 'is_remote' => 0, 'is_addhead' => 1],
                ['path' => 'plugin/bmplugin.js', 'is_pack' => 1, 'is_remote' => 0, 'is_addhead' => 1],
                ['path' => 'plugin/bmcommon.js', 'is_pack' => 1, 'is_remote' => 0, 'is_addhead' => 1],
                ['path' => 'plugin/validator.js', 'is_pack' => 1, 'is_remote' => 0, 'is_addhead' => 1]
            ],
            /**
             * css
             */
            'css' => [
                ['path' => 'common/commonpc.css', 'is_pack' => 1, 'is_remote' => 0],
                ['path' => 'plugin/elementui.css', 'is_pack' => 0, 'is_remote' => 0],
                ['path' => 'plugin/iconfont.min.css', 'is_pack' => 0, 'is_remote' => 0]
            ]
        ];
    }

}
