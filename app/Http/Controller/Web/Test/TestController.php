<?php

namespace App\Http\Controller\Web\Test;

use App\Http\Model\Web\Test\TestModel;
use Framework\Service\Foundation\Controller as BaseController;

class TestController extends BaseController {

    /**
     * 测试实例
     */
    protected $objTestModel;

    /**
     * 控制器方法对应的中间件
     * 方法名:方法对应的中间件
     */
    protected $arrMiddleware = [
    ];

    /**
     * 依赖注入，使用外部类
     */
    public function __construct(TestModel $objTestModel) {
        $this->objTestModel = $objTestModel;
    }

    /**
     * 获取视图模板里填充的数据
     * 模板,内容,js,css
     */
    protected function getViewData() {
        return [
        ];
    }

    public function testone() {
        $this->objTestModel->testone();
    }

}
