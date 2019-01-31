<?php

namespace App\Http\Model\Web\Project;

use App\Facade\Menu;
use Framework\Facade\Config;
use Framework\Facade\Request;
use Framework\Service\Database\DB;
use Framework\Service\Validation\ValidDBData;
use Framework\Service\Validation\ValidPostData;

class ProjectModel {

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
            'required' => ['value' => true, 'err_msg' => '请输入项目名称']
        ],
        'status' => [
            'optional' => ['value' => ['01', '06'], 'err_msg' => '请设置项目是否有效']
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
        $arrProjectList = $this->getProjectList($arrParam);
        //4.结果返回
        $arrData = $arrProjectList;
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
        //status
        if (in_array($arrSearchParam['status'], ['01', '06'])) {
            $strWhereSql.=' and a.status=:status';
            $arrWhereParam[':status'] = $arrSearchParam['status'];
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
    protected function getProjectList($arrParam) {
        //分页
        $intPageSize = $arrParam['page_size'];
        $intStart = ($arrParam['page_index'] - 1) * $intPageSize;

        //查询
        $strSql = "select a.id,a.cname,a.status
                    from project a
                    where 1=1 {$arrParam['where']['sql']}";
        $arrParams = $arrParam['where']['param'];
        $intTotal = 0;
        $arrProjectList = $this->objDB->setMainTable('project')->selectPage($strSql, $intStart, $intPageSize, $intTotal, $arrParams, true);

        //返回
        return [
            'list' => $arrProjectList,
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
        $arrData['person'] = $this->getPersonInfo();
        //4.结果返回        
        return true;
    }

    /**
     * 获取人员
     */
    protected function getPersonInfo() {
        //查询
        $strSql = "select a.id,a.cname label,b.cname role 
                    from account a
                        join role b on a.role_id=b.id
                        where a.status=:status
                        order by a.role_id";
        $arrParams[':status'] = '01';
        $arrPersonInfo = $this->objDB->setMainTable('account')->select($strSql, $arrParams);

        //获取人员的树状结构
        $arrRoot[] = [
            'id' => 0,
            'label' => '人员',
            'children' => $this->getChildren($arrPersonInfo)
        ];

        //返回
        return $arrRoot;
    }

    /**
     * 将人员处理为树状结构
     */
    protected function getChildren($arrPersonInfo) {
        $arrChildren = [];
        //获取角色
        $arrRole = array_unique(array_column($arrPersonInfo, 'role'));
        //根据角色分类人员
        foreach ($arrRole as $strRole) {
            $arrChildren[] = [
                'id' => $strRole,
                'label' => $strRole,
                'children' => array_values(array_filter($arrPersonInfo, function($value) use ($strRole) {
                                    return $value['role'] == $strRole;
                                }))
            ];
        }
        //返回
        return $arrChildren;
    }

    // -------------------------------------- loadProjectInfo -------------------------------------- //

    /**
     * 加载账号信息
     */
    public function loadProjectInfo(&$strErrMsg, &$arrData) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkLoadProjectInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $arrProjectInfo = $this->getProjectInfo($arrParam);
        $arrProjectInfo['person'] = $this->getProjectPersonInfo($arrParam);
        //4.结果返回
        $arrData['info'] = $arrProjectInfo;
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkLoadProjectInfo(&$arrParam) {
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
    protected function getProjectInfo($arrParam) {
        //查询
        $strSql = "select id,cname,status from project where id=:id";
        $arrParams[':id'] = $arrParam['id'];
        $arrProjectInfo = $this->objDB->setMainTable('project')->select($strSql, $arrParams);

        //返回
        return $arrProjectInfo[0];
    }

    /**
     * 获取项目的权限数据
     */
    protected function getProjectPersonInfo($arrParam) {
        //查询
        $strSql = "select account_id from projectperson where project_id=:id and status=:status";
        $arrParams[':id'] = $arrParam['id'];
        $arrParams[':status'] = '01';
        $arrPersonInfo = $this->objDB->setMainTable('projectperson')->select($strSql, $arrParams);

        //返回
        return array_values(array_column($arrPersonInfo, 'account_id'));
    }

    // -------------------------------------- saveProjectInfo -------------------------------------- //

    /**
     * 加载账号信息
     */
    public function saveProjectInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkSaveProjectInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->updateProjectInfo($arrParam);
        if ($blnRet && !empty($arrParam['id'])) {
            //移除cache文件
            Menu::delCacheFileByProjectId($arrParam['id']);
        }
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
    protected function checkSaveProjectInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        if ($arrParam['id'] == '0') {
            unset($arrRules['id']);
        }
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['id', 'cname', 'status'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查    
        $arrPerson = json_decode($arrParam['person'], true);
        $arrPerson = array_values(array_filter($arrPerson, function($value) {
                    return checkFormat($value, Config::get('const.ValidFormat.FORMAT_POSINT'));
                }));
        if (empty($arrPerson)) {
            return '请设置项目人员';
        }
        //5.其它参数
        $arrParam['person'] = array_values($arrPerson);
    }

    /**
     * 保存数据
     */
    protected function updateProjectInfo($arrParam) {
        //param
        $arrParams = [
            ':id' => $arrParam['id'],
            ':cname' => $arrParam['cname'],
            ':status' => $arrParam['status']
        ];
        //开启事务
        $this->objDB->setMainTable('project')->beginTran();
        //1.project
        if ($arrParams[':id'] == 0) {
            unset($arrParams[':id']);
            $strSql = 'insert into project(cname,status) values(:cname,:status)';
            //exec
            $intProjectId = $this->objDB->setMainTable('project')->insert($strSql, $arrParams);
            if ($intProjectId < 0) {
                $this->objDB->setMainTable('project')->rollbackTran();
                return false;
            }
        } else {
            $strSql = "update project set cname=:cname,status=:status,update_date=now() where id=:id";
            //exec
            $intRet = $this->objDB->setMainTable('project')->update($strSql, $arrParams);
            if ($intRet != 1) {
                $this->objDB->setMainTable('project')->rollbackTran();
                return false;
            }
        }

        //2.projectperson
        //2.1.删除
        $strSql = 'update projectperson set status=:status,update_date=now() where project_id=:project_id';
        $arrParams = [
            ':project_id' => $arrParam['id'],
            ':status' => '06'
        ];
        $intRet = $this->objDB->setMainTable('project')->update($strSql, $arrParams, true);
        if ($intRet < 0) {
            $this->objDB->setMainTable('project')->rollbackTran();
            return false;
        }

        //2.2.插入
        $arrParams = [
            ':project_id' => $arrParam['id'] == 0 ? $intProjectId : $arrParam['id'],
            ':status' => '01'
        ];
        $strAuth = '';
        for ($i = 0, $j = count($arrParam['person']); $i < $j; $i++) {
            if (is_numeric($arrParam['person'][$i])) {
                $strAuth.=":personid{$i},";
                $arrParams[":personid{$i}"] = $arrParam['person'][$i];
            }
        }
        $strAuth = trim($strAuth, ',');
        $strSql = "insert into projectperson(project_id,account_id) select :project_id,id from account where status=:status and id in ({$strAuth})";
        $intRet = $this->objDB->setMainTable('project')->insert($strSql, $arrParams, false);
        if ($intRet < 1) {
            $this->objDB->setMainTable('project')->rollbackTran();
            return false;
        }
        //提交事务
        $this->objDB->setMainTable('project')->commitTran();
        //返回
        return true;
    }

    // -------------------------------------- validator -------------------------------------- //
}
