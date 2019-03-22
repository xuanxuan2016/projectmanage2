<?php

namespace App\Http\Model\Web\Task;

use Framework\Facade\App;
use Framework\Facade\Log;
use Framework\Facade\User;
use Framework\Facade\Config;
use Framework\Facade\Request;
use Framework\Service\Database\DB;
use Framework\Service\Validation\ValidPostData;

class OnlineErrorModel {

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
            'required' => ['value' => true, 'err_msg' => '请输入问题标题']
        ],
        'error_desc' => [
            'required' => ['value' => true, 'err_msg' => '请输入问题描述']
        ],
        'error_solve' => [
            'required' => ['value' => true, 'err_msg' => '请输入解决方法']
        ],
        'error_summary' => [
            'required' => ['value' => true, 'err_msg' => '请输入问题总结']
        ],
        'project_id' => [
            'type' => ['value' => 'posint', 'err_msg' => '请选择所属项目']
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
        $arrOnlineErrorList = $this->getOnlineErrorList($arrParam);
        //4.结果返回
        $arrData = $arrOnlineErrorList;
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkLoadList(&$arrParam) {
        //1.获取页面参数
        $arrParam = [
            'page_index' => Request::getParam('page_index'),
            'page_size' => Request::getParam('page_size')
        ];

        //2.字段自定义配置检查
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['page_index', 'page_size'], $this->arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
        $arrSearchParam = json_decode(Request::getParam('search_param'), true);
        $strWhereSql = '';
        $arrWhereParam = [];
        //keyword
        if (!empty($arrSearchParam['keyword'])) {
            $strWhereSql .= ' and (locate(:keyword,a.title)>0 or locate(:keyword,a.error_desc)>0 or locate(:keyword,a.error_solve)>0 or locate(:keyword,a.error_summary)>0)';
            $arrWhereParam[':keyword'] = $arrSearchParam['keyword'];
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
    protected function getOnlineErrorList($arrParam) {
        //分页
        $intPageSize = $arrParam['page_size'];
        $intStart = ($arrParam['page_index'] - 1) * $intPageSize;

        //查询
        $strSql = "select a.id,a.title,b.cname project_name,a.create_date
                    from onlineerror a
                        join project b on a.project_id=b.id
                    where 1=1 {$arrParam['where']['sql']}
                    order by a.create_date desc";
        $arrParams = $arrParam['where']['param'];
        $intTotal = 0;
        $arrOnlineErrorList = $this->objDB->setMainTable('onlineerror')->selectPage($strSql, $intStart, $intPageSize, $intTotal, $arrParams, true);

        //返回
        return [
            'list' => $arrOnlineErrorList,
            'total' => $intTotal
        ];
    }

    // -------------------------------------- loadBaseInfo -------------------------------------- //

    /**
     * 获取页面基础数据
     */
    public function loadBaseInfo(&$strErrMsg, &$arrData) {
        $arrParam = [];
        //1.参数验证
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $arrData['project'] = $this->getProjectInfo();
        //4.结果返回        
        return true;
    }

    /**
     * 获取项目
     */
    protected function getProjectInfo() {
        //查询
        $strSql = "select a.id value,a.cname label
                    from project a
                    where a.status='01'";

        return $this->objDB->setMainTable('project')->select($strSql);
    }

    // -------------------------------------- loadOnlineErrorInfo -------------------------------------- //

    /**
     * 加载信息
     */
    public function loadOnlineErrorInfo(&$strErrMsg, &$arrData) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkLoadOnlineErrorInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $arrOnlineErrorInfo = $this->getOnlineErrorInfo($arrParam);
        //4.结果返回
        $arrData['info'] = $arrOnlineErrorInfo;
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkLoadOnlineErrorInfo(&$arrParam) {
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
    protected function getOnlineErrorInfo($arrParam) {
        //查询
        $strSql = 'select a.id,a.title,a.project_id,a.error_desc,a.error_solve,a.error_summary
                    from onlineerror a
                    where a.id=:id';
        $arrParams[':id'] = $arrParam['id'];
        $arrOnlineErrorInfo = $this->objDB->setMainTable('onlineerror')->select($strSql, $arrParams);

        //返回
        return $arrOnlineErrorInfo[0];
    }

    // -------------------------------------- saveOnlineErrorInfo -------------------------------------- //

    /**
     * 编辑信息
     */
    public function editOnlineErrorInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkSaveOnlineErrorInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->editOnlineError($arrParam);
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
    protected function checkSaveOnlineErrorInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        if ($arrParam['id'] == '0') {
            unset($arrRules['id']);
        }
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['id', 'title', 'project_id', 'error_desc', 'error_solve', 'error_summary'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
    }

    /**
     * 保存数据
     */
    protected function editOnlineError($arrParam) {
        //param
        $arrParams = [
            ':id' => $arrParam['id'],
            ':title' => $arrParam['title'],
            ':project_id' => $arrParam['project_id'],
            ':error_desc' => $arrParam['error_desc'],
            ':error_solve' => $arrParam['error_solve'],
            ':error_summary' => $arrParam['error_summary']
        ];
        //sql
        if ($arrParams[':id'] == 0) {
            unset($arrParams[':id']);
            $strSql = 'insert into onlineerror(title,project_id,error_desc,error_solve,error_summary) values(:title,:project_id,:error_desc,:error_solve,:error_summary)';
            //exec
            $intRet = $this->objDB->setMainTable('onlineerror')->insert($strSql, $arrParams, false);
        } else {
            $strSql = "update onlineerror set title=:title,project_id=:project_id,error_desc=:error_desc,error_solve=:error_solve,error_summary=:error_summary,update_date=now() where id=:id";
            //exec
            $intRet = $this->objDB->setMainTable('onlineerror')->update($strSql, $arrParams);
        }
        //返回
        return $intRet == 1 ? true : false;
    }

    // -------------------------------------- validator -------------------------------------- //
    // -------------------------------------- common -------------------------------------- //
}
