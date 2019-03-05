<?php

namespace App\Http\Model\Web\Task;

use Framework\Facade\App;
use Framework\Facade\Log;
use Framework\Facade\User;
use Framework\Facade\Config;
use Framework\Facade\Request;
use Framework\Service\Database\DB;
use Framework\Service\Validation\ValidPostData;

class TodoModel {

    /**
     * 数据实例
     */
    protected $objDB;

    /**
     * 字段数据库配置检查实例
     */
    protected $objValidPostData;

    /**
     * post参数校验配置
     */
    protected $arrRules = [
        'id' => [
            'type' => ['value' => 'posint', 'err_msg' => 'id格式不正确']
        ],
        'page_index' => [
            'type' => ['value' => 'posint', 'err_msg' => 'page_index格式不正确']
        ],
        'page_size' => [
            'type' => ['value' => 'posint', 'err_msg' => 'page_size格式不正确']
        ],
        'title' => [
            'required' => ['value' => true, 'err_msg' => '请输入事项标题']
        ],
        'content' => [
            'required' => ['value' => true, 'err_msg' => '请输入事项内容']
        ],
        'priority' => [
            'optional' => ['value' => [1, 2, 3], 'err_msg' => '请设置事项级别']
        ],
        'status' => [
            'optional' => ['value' => ['01', '02', '06'], 'err_msg' => '请设置事项状态']
        ]
    ];

    /**
     * 构造方法
     */
    public function __construct(DB $objDB, ValidPostData $objValidPostData) {
        $this->objDB = $objDB;
        $this->objValidPostData = $objValidPostData;
    }

    // -------------------------------------- loadList -------------------------------------- //

    /**
     * 获取列表数据
     */
    public function loadList(&$strErrMsg, &$arrData) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkLoadList($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $arrTodoList = $this->getTodoList($arrParam);
        //4.结果返回
        $arrData = $arrTodoList;
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkLoadList(&$arrParam) {
        //1.获取页面参数
        //2.字段自定义配置检查
        //3.字段数据库配置检查
        //4.业务检查
        $arrSearchParam = json_decode(Request::getParam('search_param'), true);
        $strWhereSql = '';
        $arrWhereParam = [];
        //title/content
        if (!empty($arrSearchParam['title'])) {
            $strWhereSql .= ' and (locate(:title,a.title)>0 or locate(:title,a.content)>0)';
            $arrWhereParam[':title'] = $arrSearchParam['title'];
        }
        //status
        if (!empty($arrSearchParam['status'])) {
            $strWhereSqlTmp = '';
            $arrWhereParamTmp = [];
            for ($i = 0, $j = count($arrSearchParam['status']); $i < $j; $i++) {
                if (in_array($arrSearchParam['status'][$i], $this->arrRules['status']['optional']['value'])) {
                    $strWhereSqlTmp .= ":status{$i},";
                    $arrWhereParamTmp[":status{$i}"] = $arrSearchParam['status'][$i];
                }
            }
            if (!empty($arrWhereParamTmp)) {
                $strWhereSqlTmp = trim($strWhereSqlTmp, ',');
                $strWhereSql .= " and a.status in ($strWhereSqlTmp)";
                $arrWhereParam = array_merge($arrWhereParam, $arrWhereParamTmp);
            }
        } else {
            $strWhereSql .= ' and a.status=:status';
            $arrWhereParam[':status'] = '-1';
        }

        //5.其它参数
        $arrParam['where'] = [
            'sql' => $strWhereSql,
            'param' => $arrWhereParam
        ];
    }

    /**
     * 获取数据
     */
    protected function getTodoList($arrParam) {
        //查询
        $strSql = "select a.id,a.title,a.content,a.priority,a.status,a.create_date
                    from todo a
                    where 1=1 and a.account_id=:account_id {$arrParam['where']['sql']}
                    order by a.status asc,a.priority desc,a.create_date asc";
        $arrParams = $arrParam['where']['param'];
        $arrParams[':account_id'] = User::getAccountId();
        $arrTodoList = $this->objDB->setMainTable('todo')->select($strSql, $arrParams);

        //返回
        return [
            'list' => $arrTodoList
        ];
    }

    // -------------------------------------- loadTodoInfo -------------------------------------- //

    /**
     * 加载信息
     */
    public function loadTodoInfo(&$strErrMsg, &$arrData) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkLoadTodoInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $arrTodoInfo = $this->getTodoInfo($arrParam);
        //4.结果返回
        $arrData['info'] = $arrTodoInfo;
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkLoadTodoInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = [
            'id' => Request::getParam('id')
        ];

        //2.字段自定义配置检查
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['id'], $this->arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }
        //3.字段数据库配置检查
        //4.业务检查
    }

    /**
     * 获取数据
     */
    protected function getTodoInfo($arrParam) {
        //查询
        $strSql = 'select a.id,a.title,a.content,a.priority
                    from todo a
                    where a.id=:id and a.account_id=:account_id';
        $arrParams[':id'] = $arrParam['id'];
        $arrParams[':account_id'] = User::getAccountId();
        $arrTodoInfo = $this->objDB->setMainTable('todo')->select($strSql, $arrParams);

        //返回
        return $arrTodoInfo[0];
    }

    // -------------------------------------- saveTodoInfo -------------------------------------- //

    /**
     * 编辑信息
     */
    public function editTodoInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkSaveTodoInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->editTodo($arrParam);
        //4.结果返回
        if (!$blnRet) {
            $strErrMsg = '保存失败';
            return false;
        }
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkSaveTodoInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        if ($arrParam['id'] == '0') {
            unset($arrRules['id']);
        }
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['id', 'title', 'content', 'priority'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
    }

    /**
     * 保存数据
     */
    protected function editTodo($arrParam) {
        //param
        $arrParams = [
            ':id' => $arrParam['id'],
            ':title' => $arrParam['title'],
            ':content' => $arrParam['content'],
            ':priority' => $arrParam['priority'],
            ':account_id' => User::getAccountId(),
        ];
        //sql
        if ($arrParams[':id'] == 0) {
            unset($arrParams[':id']);
            $strSql = 'insert into todo(title,content,priority,account_id) values(:title,:content,:priority,:account_id)';
            //exec
            $intRet = $this->objDB->setMainTable('todo')->insert($strSql, $arrParams, false);
        } else {
            $strSql = "update todo set title=:title,content=:content,priority=:priority,update_date=now() where id=:id and account_id=:account_id";
            //exec
            $intRet = $this->objDB->setMainTable('todo')->update($strSql, $arrParams);
        }
        //返回
        return $intRet == 1 ? true : false;
    }

    // -------------------------------------- doneTodoInfo -------------------------------------- //

    /**
     * 完成
     */
    public function doneTodoInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkDoneTodoInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->doneTodo($arrParam);
        //4.结果返回
        if (!$blnRet) {
            $strErrMsg = '保存失败';
            return false;
        }
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkDoneTodoInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['id'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
    }

    /**
     * 完成
     */
    protected function doneTodo($arrParam) {
        $arrParams = [
            ':id' => $arrParam['id'],
            ':account_id' => User::getAccountId(),
            ':status' => '02'
        ];
        $strSql = 'update todo set status=:status,update_date=now() where id=:id and account_id=:account_id';
        $intRet = $this->objDB->setMainTable('todo')->update($strSql, $arrParams);
        return $intRet == 1 ? true : false;
    }

    // -------------------------------------- deleteTodoInfo -------------------------------------- //

    /**
     * 删除
     */
    public function deleteTodoInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkDeleteTodoInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->deleteTodo($arrParam);
        //4.结果返回
        if (!$blnRet) {
            $strErrMsg = '保存失败';
            return false;
        }
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkDeleteTodoInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['id'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
    }

    /**
     * 删除
     */
    protected function deleteTodo($arrParam) {
        $arrParams = [
            ':id' => $arrParam['id'],
            ':account_id' => User::getAccountId(),
            ':status' => '06'
        ];
        $strSql = 'update todo set status=:status,update_date=now() where id=:id and account_id=:account_id';
        $intRet = $this->objDB->setMainTable('todo')->update($strSql, $arrParams);
        return $intRet == 1 ? true : false;
    }

    // -------------------------------------- validator -------------------------------------- //
    // -------------------------------------- common -------------------------------------- //
}
