# 目录

* [命名规范](#1)
* [常用快捷方法](#2)
* [公用方法](#3)
* [控制器Controller](#4)
* [业务处理Model](#5)
* [代码格式](#6)

---

### <div id="1">命名规范</div>

变量命名规则，包含布尔型、整型、小数型、字符串型、数组型、对象型、日期型、伪类型。

```
布尔型变量(boolean)：
$blnVar;
整型变量(integer)：
$intVar;
小数型变量(decimal)：
$decVar;
字符串型变量(string)：
$strVar;
数组型变量(array)：
$arrVar;
对象型变量(object)：
$objVar;
日期型变量(datetime)：
$dateVar;
伪类型变量(mix)（可以接受不同的类型）：
$mixVar;
```

类命名规则，包含Controller类、Model类、Service类，采用驼峰命名法。

Controller类（控制器）：加载页面配置与接收页面请求

```
<?php

class GetTestController extends BaseController {
    /*具体处理内容*/
}
```

Model类（模型）：具体业务处理逻辑

```
<?php

class InviteModel {
    /*具体处理内容*/
}
```

Service类（服务）：通用方法、api等

```
<?php
namespace App\Service\Invite;

class FreUsedFunc {
    /*具体通用方法*/
}
```

类的方法名、参数名、成员变量、局部变量都统一使用lowerCamelCase风格，必须遵从驼峰形式。
> localValue / getHttpMessage() / inputUserId

常量命名全部大写，单词间用下划线隔开，力求语义表达完整清楚，不要嫌名字长。
> 如使用MAX_STOCK_COUNT，而不要使用MAX_COUNT。

---

### <div id="2">常用快捷方法</div>

在开发后台需求时，常用的一些方法。更多其它方法，请参见【公用方法】里的内容。

```
Model类
获取页面参数：
    Tips:使用下面2个方法获取到的值会被html编码掉，如果想要获取原来值请用$_REQUEST['参数名']获取
    获取单个参数：Request::getParam('参数名');;
    获取所有参数：Request::getAllParam();
获取当前登录账号信息：
    获取所有信息：User::getAllInfo();
    获取账号ID：User::getAccountId();
判断当前请求是否为ajax：
    Request::isAjax();
获取guid：
    getGUID();
```

---

### <div id="3">公用方法</div>

Facades

> 为应用程序的服务容器中可用的类提供了一个「静态」接口。
通过使用Facade，我们可以使用大部分的公用方法。
详细方法请查看【framework\\Facade\\】或【app\\Facade\\】下的相关文件。

```
use Framework\Facade\Request;

$arrParam['invite_id'] = Request::getParam('invite_id');
```    
---
helpers

>全局通用方法，可直接在文件中使用其中定义的相关方法。
详细方法请查看【framework\\Service\\Foundation\\helpers.php】文件
```
$strGuid = getGUID();
$strDateTime = getMicroTimeStamp();
```

---
### <div id="4">控制器Controller</div>

##### controlle代码模板

<p>
Tips：此为格式说明，实际业务中请按需处理。
</p>

```
<?php

namespace App\Http\Controller\Web\Task;

use App\Facade\Menu;
use Framework\Facade\User;

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
        'loadList' => [[CheckAuthButton::class, 'Task.ToDo']]
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
                'title' => '待办事项'
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
     * 获取列表数据
     */
    public function loadList() {
        $strErrMsg = '';
        $arrData = [];
        $blnFlag = $this->objTodoModel->loadList($strErrMsg, $arrData);
        return ['success' => $blnFlag ? 1 : 0, 'err_msg' => $strErrMsg, 'data' => $arrData];
    }

}
```

---

### <div id="5">业务处理Model</div>

<p>
一段规范的model处理方法，包含如下几个部分：
</p>

- 1.方法块注释，便于折叠代码时能快速了解代码功能
- 2.主model方法，包含多个子方法（参数验证，记录操作日志，业务逻辑，结果返回等）
- 3.参数验证方法，用于获取前端过来的参数，对参数进行格式与业务逻辑校验
- 4.如果多个主model方法可通用的子方法的话，可放在common块里

##### model代码模板

<p>
Tips：此为格式说明，实际业务中请按需处理。
</p>

```
<?php

namespace App\Http\Model\Web\Task;

use Framework\Facade\App;
use Framework\Service\Database\DB;

class TodoModel {
    /**
     * 数据实例
     */
    protected $objDB;
    
    /**
     * 构造方法，依赖注入
     */
    public function __construct(DB $objDB) {
        $this->objDB = $objDB;
    }
    
    // -------------------------------------- loadList -------------------------------------- //

    /**
     * 主model方法：获取列表数据
     * 在写代码时，以下每个步骤都需要有，如果实际确实没操作，内容可以空，注释需要保留；实际内容，可根据业务做相关调整
     */
    public function loadList(&$strErrMsg, &$arrData) {
        //1.参数验证
        $strErrMsg = $this->checkLoadList($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $arrData = $this->getTeacherbookList($arrParam);
        //4.结果返回
    }

    /**
     * 参数验证方法：参数检查
     */
    protected function checkLoadList(&$arrParam) {
        //1.获取页面参数
        $arrCheckResult = $this->objValidPostData->check(['lecturer_id', 'date'], $this->arrRules, [], false);
        if (!$arrCheckResult['success']) {
            return $arrCheckResult['err_msg'];
        }
        $arrParam = $arrCheckResult['param'];
        //2.字段自定义配置检查
        //3.字段数据库配置检查
        //4.业务检查
    }

    /**
     * 获取数据，具体逻辑
     */
    protected function getTeacherbookList($arrParam) {
        //...
    }
    
    // -------------------------------------- common -------------------------------------- //
    
    /**
     * model通用方法
     */
    protected function commonFunc() {
    }
}
```


---

### <div id="6">代码格式</div>

为了使代码阅读起来流畅，请注意代码格式。

> 使用NetBeans开发工具时，可通过【右键->格式】来方便的格式化代码。
