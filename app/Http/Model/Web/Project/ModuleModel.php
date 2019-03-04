<?php

namespace App\Http\Model\Web\Project;

use Framework\Facade\User;
use Framework\Facade\Config;
use Framework\Facade\Request;
use Framework\Service\Database\DB;
use Framework\Service\Validation\ValidDBData;
use Framework\Service\Validation\ValidPostData;

class ModuleModel {

    /**
     * 数据实例
     */
    protected $objDB;

    /**
     * 字段数据库配置检查实例
     */
    protected $objValidDBData;

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
        'cname' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请输入模块名称']
        ],
        'project_id' => [
            'type' => ['value' => 'posint', 'err_msg' => '请设置模块项目']
        ],
        'type' => [
            'optional' => ['value' => ['01', '02'], 'err_msg' => '请设置模块类别']
        ]
    ];

    /**
     * 构造方法
     */
    public function __construct(DB $objDB, ValidDBData $objValidDBData, ValidPostData $objValidPostData) {
        $this->objDB = $objDB;
        $this->objValidDBData = $objValidDBData;
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
        $arrModuleList = $this->getModuleList($arrParam);
        //4.结果返回
        $arrData = $arrModuleList;
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkLoadList(&$arrParam) {
        //1.获取页面参数
        $arrParam = [
            'page_index' => Request::getParam('page_index'),
            'page_size' => Request::getParam('page_size'),
            'project_id' => Request::getParam('project_id')
        ];

        //2.字段自定义配置检查
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['page_index', 'page_size', 'project_id'], $this->arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
        $arrSearchParam = json_decode(Request::getParam('search_param'), true);
        $strWhereSql = '';
        $arrWhereParam = [];
        //type
        if (in_array($arrSearchParam['type'], ['01', '02'])) {
            $strWhereSql.=' and a.type=:type';
            $arrWhereParam[':type'] = $arrSearchParam['type'];
        }
        //project_id
        $strWhereSql.=' and a.project_id=:project_id';
        $arrWhereParam[':project_id'] = $arrSearchParam['project_id'];

        //5.其它参数
        $arrParam['where'] = [
            'sql' => $strWhereSql,
            'param' => $arrWhereParam
        ];
    }

    /**
     * 获取数据
     */
    protected function getModuleList($arrParam) {
        //分页
        $intPageSize = $arrParam['page_size'];
        $intStart = ($arrParam['page_index'] - 1) * $intPageSize;

        //查询
        $strSql = "select a.id,a.project_id,a.type,a.cname,b.cname projectname
                    from module a
                    join project b on a.project_id=b.id
                    where 1=1 and a.status=:status {$arrParam['where']['sql']}
                    order by a.project_id,a.type,a.cname";
        $arrParams = $arrParam['where']['param'];
        $arrParams[':status'] = '01';
        $intTotal = 0;
        $arrModuleList = $this->objDB->setMainTable('module')->selectPage($strSql, $intStart, $intPageSize, $intTotal, $arrParams, true);

        //返回
        return [
            'list' => $arrModuleList,
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
        $strSql = "select a.id,a.cname
                    from project a
                        join projectperson b on a.id=b.project_id
                        where a.status=:status and b.status=:status and b.account_id=:account_id";
        $arrParams = [
            ':status' => '01',
            ':account_id' => User::getAccountId()
        ];
        $arrProject = $this->objDB->setMainTable('project')->select($strSql, $arrParams);

        //返回
        return $arrProject;
    }

    // -------------------------------------- loadModuleInfo -------------------------------------- //

    /**
     * 加载账号信息
     */
    public function loadModuleInfo(&$strErrMsg, &$arrData) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkLoadModuleInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $arrModuleInfo = $this->getModuleInfo($arrParam);
        //4.结果返回
        $arrData['info'] = $arrModuleInfo;
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkLoadModuleInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = [
            'id' => Request::getParam('id'),
            'project_id' => Request::getParam('project_id')
        ];

        //2.字段自定义配置检查
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['id', 'project_id'], $this->arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }
        //3.字段数据库配置检查
        //4.业务检查
    }

    /**
     * 获取数据
     */
    protected function getModuleInfo($arrParam) {
        //查询
        $strSql = "select id,cname,project_id,type from module where id=:id";
        $arrParams[':id'] = $arrParam['id'];
        $arrModuleInfo = $this->objDB->setMainTable('module')->select($strSql, $arrParams);

        //返回
        return $arrModuleInfo[0];
    }

    // -------------------------------------- saveModuleInfo -------------------------------------- //

    /**
     * 加载账号信息
     */
    public function saveModuleInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkSaveModuleInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->updateModuleInfo($arrParam);
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
    protected function checkSaveModuleInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        if ($arrParam['id'] == '0') {
            unset($arrRules['id']);
        }
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['id', 'cname', 'type', 'project_id'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
    }

    /**
     * 保存数据
     */
    protected function updateModuleInfo($arrParam) {
        //param
        $arrParams = [
            ':id' => $arrParam['id'],
            ':cname' => $arrParam['cname'],
            ':project_id' => $arrParam['project_id'],
            ':type' => $arrParam['type']
        ];
        //sql
        if ($arrParams[':id'] == 0) {
            unset($arrParams[':id']);
            $strSql = 'insert into module(cname,project_id,type) values(:cname,:project_id,:type)';
            //exec
            $intRet = $this->objDB->setMainTable('module')->insert($strSql, $arrParams, false);
        } else {
            $strSql = "update module set cname=:cname,project_id=:project_id,type=:type,update_date=now() where id=:id";
            //exec
            $intRet = $this->objDB->setMainTable('module')->update($strSql, $arrParams);
        }
        //返回
        return $intRet == 1 ? true : false;
    }

    // -------------------------------------- deleteModuleInfo -------------------------------------- //

    /**
     * 加载账号信息
     */
    public function deleteModuleInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkSaveModuleInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->deleteModule($arrParam);
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
    protected function checkDeleteModuleInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['id', 'project_id'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
        //todo:检查模块是否被使用
    }

    /**
     * 保存数据
     */
    protected function deleteModule($arrParam) {
        //param
        $arrParams = [
            ':id' => $arrParam['id'],
            ':status' => '06'
        ];
        //sql
        $strSql = "update module set status=:status,update_date=now() where id=:id";
        //exec
        $intRet = $this->objDB->setMainTable('module')->update($strSql, $arrParams);
        //返回
        return $intRet == 1 ? true : false;
    }

    // -------------------------------------- validator -------------------------------------- //
}
