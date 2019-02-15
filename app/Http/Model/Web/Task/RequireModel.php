<?php

namespace App\Http\Model\Web\Task;

use Framework\Facade\User;
use Framework\Facade\Config;
use Framework\Facade\Request;
use Framework\Service\Database\DB;
use Framework\Service\Validation\ValidDBData;
use Framework\Service\Validation\ValidPostData;

class RequireModel {

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
        ],
        'status' => [
            'optional' => ['value' => ['00', '01', '02', '03', '04', '05'], 'err_msg' => '请设置需求状态']
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
        $arrRequireList = $this->getRequireList($arrParam);
        //4.结果返回
        $arrData = $arrRequireList;
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
//        if (in_array($arrSearchParam['type'], ['01', '02'])) {
//            $strWhereSql .= ' and a.type=:type';
//            $arrWhereParam[':type'] = $arrSearchParam['type'];
//        }
        //project_id
        $strWhereSql .= ' and a.project_id=:project_id';
        $arrWhereParam[':project_id'] = $arrSearchParam['project_id'];
        //task_name
        if (!empty($arrSearchParam['task_name'])) {
            $strWhereSql .= ' and locate(:task_name,a.task_name)>0';
            $arrWhereParam[':task_name'] = $arrSearchParam['task_name'];
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
        //account_id
        if (!empty($arrSearchParam['account_id']) && checkFormat($arrSearchParam['account_id'], Config::get('const.ValidFormat.FORMAT_POSINT'))) {
            $strWhereSql .= ' and a.account_id=:account_id';
            $arrWhereParam[':account_id'] = $arrSearchParam['account_id'];
        }
        //needer
        if (!empty($arrSearchParam['needer'])) {
            $strWhereSql .= ' and a.needer=:needer';
            $arrWhereParam[':needer'] = $arrSearchParam['needer'];
        }
        //module
        if (!empty($arrSearchParam['module_id']) && checkFormat($arrSearchParam['account_id'], Config::get('const.ValidFormat.FORMAT_POSINT'))) {
            $strWhereSql .= ' and a.module_id=:module_id';
            $arrWhereParam[':module_id'] = $arrSearchParam['module_id'];
        } else {
            if (!empty($arrSearchParam['module_type'])) {
                $strWhereSql .= ' and b.type=:module_type';
                $arrWhereParam[':module_type'] = $arrSearchParam['module_type'];
            }
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
    protected function getRequireList($arrParam) {
        //分页
        $intPageSize = $arrParam['page_size'];
        $intStart = ($arrParam['page_index'] - 1) * $intPageSize;

        //查询
        $strSql = "select a.id,a.task_name,b.type module_type,b.cname module_name,c.cname account_name,a.needer,a.status,a.xingzhi,a.create_date,a.send_date,a.need_done_date,a.done_date
                    from task a
                        join module b on a.module_id=b.id
                        left join account c on a.account_id=b.id
                    where 1=1 {$arrParam['where']['sql']}
                    order by a.create_date desc";
        $arrParams = $arrParam['where']['param'];
        $intTotal = 0;
        $arrRequireList = $this->objDB->setMainTable('task')->selectPage($strSql, $intStart, $intPageSize, $intTotal, $arrParams, true);

        //返回
        return [
            'list' => $arrRequireList,
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
        $arrData['account'] = $this->getAccountInfo();
        $arrData['needer'] = $this->getNeederInfo();
        $arrData['module'] = $this->getModuleInfo();
        //4.结果返回        
        return true;
    }

    /**
     * 获取开发人员
     */
    protected function getAccountInfo() {
        //查询
        $strSql = "select a.id value,a.cname label,a.status 
                    from account a
                    where a.id in (select account_id from projectperson where project_id=:project_id) and a.is_can_search=1
                    order by a.status,convert(a.cname using gbk)";
        $arrParams = [
            ':project_id' => Request::getParam('project_id')
        ];
        $arrAccount = $this->objDB->setMainTable('account')->select($strSql, $arrParams);

        //获取人员的树状结构
        $arrRoot = $this->getChildren($arrAccount);

        //返回
        return $arrRoot;
    }

    /**
     * 获取提出人
     */
    protected function getNeederInfo() {
        //查询
        $strSql = "select cname label,cname value,status 
                    from needer 
                    where project_id=:project_id and is_can_search=1
                    order by status,convert(cname using gbk)";
        $arrParams = [
            ':project_id' => Request::getParam('project_id')
        ];
        $arrNeeder = $this->objDB->setMainTable('needer')->select($strSql, $arrParams);

        //获取人员的树状结构
        $arrRoot = $this->getChildren($arrNeeder);

        //返回
        return $arrRoot;
    }

    /**
     * 将数据处理为树状结构
     */
    protected function getChildren($arrPersonInfo) {
        $arrChildren = [];
        //获取状态
        $arrStatus = array_unique(array_column($arrPersonInfo, 'status'));
        //根据状态分类人员
        foreach ($arrStatus as $strStatus) {
            $arrChildren[] = [
                'label' => $strStatus == '01' ? '在职' : '离职',
                'options' => array_values(array_filter($arrPersonInfo, function($value) use ($strStatus) {
                                    return $value['status'] == $strStatus;
                                }))
            ];
        }
        //返回
        return $arrChildren;
    }

    /**
     * 获取模块
     */
    protected function getModuleInfo() {
        //查询
        $strSql = "select cname label,id value,type 
                    from module 
                    where project_id=:project_id and status='01'
                    order by type,convert(cname using gbk);";
        $arrParams = [
            ':project_id' => Request::getParam('project_id')
        ];
        $arrModule = $this->objDB->setMainTable('module')->select($strSql, $arrParams);

        //获取人员的树状结构
        $arrRoot = $this->getChildrenModule($arrModule);

        //返回
        return $arrRoot;
    }

    /**
     * 将数据处理为树状结构
     */
    protected function getChildrenModule($arrModuleInfo) {
        $arrChildren = [];
        //获取状态
        $arrType = array_unique(array_column($arrModuleInfo, 'type'));
        //根据状态分类人员
        foreach ($arrType as $strType) {
            $arrChildren[] = [
                'label' => $strType == '01' ? '系统' : '业务',
                'options' => array_values(array_filter($arrModuleInfo, function($value) use ($strType) {
                                    return $value['type'] == $strType;
                                }))
            ];
        }
        //返回
        return $arrChildren;
    }

    // -------------------------------------- loadRequireInfo -------------------------------------- //

    /**
     * 加载账号信息
     */
    public function loadRequireInfo(&$strErrMsg, &$arrData) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkLoadRequireInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $arrRequireInfo = $this->getRequireInfo($arrParam);
        //4.结果返回
        $arrData['info'] = $arrRequireInfo;
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkLoadRequireInfo(&$arrParam) {
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
    protected function getRequireInfo($arrParam) {
        //查询
        $strSql = "select id,cname,project_id,type from task where id=:id";
        $arrParams[':id'] = $arrParam['id'];
        $arrRequireInfo = $this->objDB->setMainTable('task')->select($strSql, $arrParams);

        //返回
        return $arrRequireInfo[0];
    }

    // -------------------------------------- saveRequireInfo -------------------------------------- //

    /**
     * 加载账号信息
     */
    public function saveRequireInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkSaveRequireInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->updateRequireInfo($arrParam);
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
    protected function checkSaveRequireInfo(&$arrParam) {
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
    protected function updateRequireInfo($arrParam) {
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
            $strSql = 'insert into task(cname,project_id,type) values(:cname,:project_id,:type)';
            //exec
            $intRet = $this->objDB->setMainTable('task')->insert($strSql, $arrParams, false);
        } else {
            $strSql = "update task set cname=:cname,project_id=:project_id,type=:type where id=:id";
            //exec
            $intRet = $this->objDB->setMainTable('task')->update($strSql, $arrParams);
        }
        //返回
        return $intRet == 1 ? true : false;
    }

    // -------------------------------------- deleteRequireInfo -------------------------------------- //

    /**
     * 加载账号信息
     */
    public function deleteRequireInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkSaveRequireInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->deleteRequire($arrParam);
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
    protected function checkDeleteRequireInfo(&$arrParam) {
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
    protected function deleteRequire($arrParam) {
        //param
        $arrParams = [
            ':id' => $arrParam['id'],
            ':status' => '06'
        ];
        //sql
        $strSql = "update task set status=:status where id=:id";
        //exec
        $intRet = $this->objDB->setMainTable('task')->update($strSql, $arrParams);
        //返回
        return $intRet == 1 ? true : false;
    }

    // -------------------------------------- validator -------------------------------------- //
}
