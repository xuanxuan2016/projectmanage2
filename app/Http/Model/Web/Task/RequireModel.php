<?php

namespace App\Http\Model\Web\Task;

use Framework\Facade\Log;
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
        'project_id' => [
            'type' => ['value' => 'posint', 'err_msg' => 'project_id格式不正确']
        ],
        'page_index' => [
            'type' => ['value' => 'posint', 'err_msg' => 'page_index格式不正确']
        ],
        'page_size' => [
            'type' => ['value' => 'posint', 'err_msg' => 'page_size格式不正确']
        ],
        'status' => [
            'optional' => ['value' => ['00', '01', '02', '03', '04', '05'], 'err_msg' => '请设置需求状态']
        ],
        'xingzhi' => [
            'optional' => ['value' => ['01', '02'], 'err_msg' => '请设置需求性质']
        ],
        'needer' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请输入需求提出人']
        ],
        'task_name' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请输入需求名称']
        ],
        'module_id' => [
            'type' => ['value' => 'posint', 'err_msg' => '请选择需求模块']
        ],
        'need_memo' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请输入需求明细']
        ],
        'page_enter' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请输入页面入口']
        ],
        'dev_memo' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请输入逻辑说明']
        ],
        'change_file' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请输入修改文件']
        ],
        'dev_dealy_reason' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请输入开发延迟原因']
        ],
        'change_file1' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请输入送测修改文件']
        ],
        'change_file2' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请输入送测修改文件']
        ],
        'change_file3' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请输入送测修改文件']
        ],
        'change_file4' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请输入送测修改文件']
        ],
        'change_file5' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请输入送测修改文件']
        ]
    ];

    /**
     * post参数校验配置(分配)
     */
    protected $arrRulesAllot = [
        'project_id' => [
            'type' => ['value' => 'posint', 'err_msg' => 'project_id格式不正确']
        ],
        'need_done_date' => [
            'trim' => ['value' => true],
            'type' => ['value' => 'date', 'err_msg' => '请设置期望完成时间']
        ],
        'account_id' => [
            'type' => ['value' => 'posint', 'err_msg' => '请选择开发人员']
        ],
        'task_id' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请选择需要分配的需求']
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
        $strSql = "select a.id,a.task_name,b.type module_type,b.cname module_name,c.cname account_name,d.cname needer,a.status,a.xingzhi,a.create_date,a.send_date,a.need_done_date,a.done_date
                    from task a
                        join module b on a.module_id=b.id
                        left join account c on a.account_id=c.id
                        join account d on a.needer_id=d.id
                    where 1=1 {$arrParam['where']['sql']}
                    order by a.create_date desc";
        $arrParams = $arrParam['where']['param'];
        $intTotal = 0;
        $arrRequireList = $this->objDB->setMainTable('task')->selectPage($strSql, $intStart, $intPageSize, $intTotal, $arrParams, true);

        //额外状态处理
        foreach ($arrRequireList as &$arrTmp) {
            //需求是否超时
            if (!empty($arrTmp['need_done_date']) && $arrTmp['status'] == '02') {
                $arrTmp['is_timeout'] = strtotime(date('Y-m-d')) > strtotime(date('Y-m-d', strtotime($arrTmp['need_done_date']))) ? 1 : 0;
            } else {
                $arrTmp['is_timeout'] = 0;
            }
        }

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
        $arrData['account_allot'] = $this->getAccountAllotInfo();
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
                    where a.id in (select distinct account_id from task where project_id=:project_id) and a.is_can_search=1
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
        $strSql = "select a.id value,a.cname label,a.status 
                    from account a
                    where a.id in (select distinct needer_id from task where project_id=:project_id) and a.is_can_search=1
                    order by a.status,convert(a.cname using gbk)";
        $arrParams = [
            ':project_id' => Request::getParam('project_id')
        ];
        $arrNeeder = $this->objDB->setMainTable('account')->select($strSql, $arrParams);

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

    /**
     * 获取分配人员
     */
    protected function getAccountAllotInfo() {
        //查询
        $strSql = "select a.id value,CONCAT(a.cname,'(',ifnull(c.count,0),')') label,b.cname role 
                    from account a
                        join role b on a.role_id=b.id and b.cname in ('admin','manager','devloper')
                        left join (select account_id,count(*) count from task where project_id=:project_id and status='02' group by account_id)c on a.id=c.account_id
                        join projectperson d on a.id=d.account_id and d.project_id=:project_id and d.status='01'
                        where a.status='01'
                    order by convert(a.cname using gbk)";
        $arrParams = [
            ':project_id' => Request::getParam('project_id')
        ];
        $arrAccount = $this->objDB->setMainTable('account')->select($strSql, $arrParams);

        //获取人员的树状结构
        $arrRoot = $this->getChildrenAllot($arrAccount);

        //返回
        return $arrRoot;
    }

    /**
     * 将数据处理为树状结构
     */
    protected function getChildrenAllot($arrModuleInfo) {
        $arrChildren = [];
        $arrChildren[] = [
            'label' => '开发',
            'options' => array_values(array_filter($arrModuleInfo, function($value) {
                                return $value['role'] == 'devloper';
                            }))
        ];
        $arrChildren[] = [
            'label' => '主管',
            'options' => array_values(array_filter($arrModuleInfo, function($value) {
                                return in_array($value['role'], ['admin', 'manager']);
                            }))
        ];
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
        $strSql = 'select a.id,a.status,a.account_id,a.need_done_date,
                        a.xingzhi,a.needer,a.task_name,b.type module_type,a.module_id,a.need_memo,a.need_attach,
                        a.page_enter,a.dev_memo,a.need_tip,a.change_file,a.sql_attach,a.other_attach,a.dev_dealy_reason,
                        ifnull(c.round,0) round,a.change_file1,a.change_file2,a.change_file3,a.change_file4,a.change_file5
                    from task a
                        join module b on a.module_id=b.id
                        left join qa c on a.qa_batch_id=c.batch_id
                    where a.id=:id and a.project_id=:project_id';
        $arrParams[':id'] = $arrParam['id'];
        $arrParams[':project_id'] = $arrParam['project_id'];
        $arrRequireInfo = $this->objDB->setMainTable('task')->select($strSql, $arrParams);

        //额外状态处理
        foreach ($arrRequireInfo as &$arrTmp) {
            //需求是否超时
            if (!empty($arrTmp['need_done_date']) && $arrTmp['status'] == '02') {
                $arrTmp['is_timeout'] = strtotime(date('Y-m-d')) > strtotime(date('Y-m-d', strtotime($arrTmp['need_done_date']))) ? 1 : 0;
            } else {
                $arrTmp['is_timeout'] = 0;
            }

            //需求是否为自身
            if ($arrTmp['account_id'] == User::getAccountId()) {
                $arrTmp['is_self'] = 1;
            } else {
                $arrTmp['is_self'] = 0;
            }

            //送测轮次
            $arrTmp['change_file_qa'][1] = $arrTmp['change_file1'];
            $arrTmp['change_file_qa'][2] = $arrTmp['change_file2'];
            $arrTmp['change_file_qa'][3] = $arrTmp['change_file3'];
            $arrTmp['change_file_qa'][4] = $arrTmp['change_file4'];
            $arrTmp['change_file_qa'][5] = $arrTmp['change_file5'];
        }

        //返回
        return $arrRequireInfo[0];
    }

    // -------------------------------------- addRequireInfo -------------------------------------- //

    /**
     * 新建需求
     */
    public function addRequireInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkAddRequireInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->addRequire($arrParam);
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
    protected function checkAddRequireInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['xingzhi', 'task_name', 'module_id', 'need_memo', 'need_attach'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
    }

    /**
     * 新建需求
     */
    protected function addRequire($arrParam) {
        //param
        $arrParams = [
            ':project_id' => $arrParam['project_id'],
            ':xingzhi' => $arrParam['xingzhi'],
            ':needer_id' => User::getAccountId(),
            ':task_name' => $arrParam['task_name'],
            ':module_id' => $arrParam['module_id'],
            ':need_memo' => $arrParam['need_memo'],
            ':need_attach' => $arrParam['need_attach'],
        ];
        //sql
        $strSql = 'insert into task(project_id,xingzhi, needer_id,task_name,module_id,need_memo,need_attach) values(:project_id,:xingzhi,:needer_id,:task_name,:module_id,:need_memo,:need_attach)';
        $intRet = $this->objDB->setMainTable('task')->insert($strSql, $arrParams, false);
        //返回
        return $intRet == 1 ? true : false;
    }

    // -------------------------------------- editRequireInfo -------------------------------------- //

    /**
     * 编辑需求
     */
    public function editRequireInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkEditRequireInfo($arrParam, $arrSaveCol);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->editRequire($arrParam, $arrSaveCol);
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
    protected function checkEditRequireInfo(&$arrParam, &$arrSaveCol) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        $arrSaveCol = $this->getSaveCol();
        if (empty($arrSaveCol)) {
            return '获取保存字段失败';
        }
        $arrErrMsg = $this->objValidPostData->check($arrParam, array_merge(['id', 'project_id'], $arrSaveCol), $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
    }

    /**
     * 编辑需求
     */
    protected function editRequire($arrParam, $arrSaveCol) {
        $strSql = '';
        $strWhere = '';
        $arrParams = [
            ':id' => $arrParam['id'],
            ':project_id' => $arrParam['project_id']
        ];
        //非管理人员与产品(产品tab字段)
        //只能修改自己的需求
        if (!in_array(User::getAccountRoleName(), ['admin', 'manager', 'product'])) {
            $strWhere .= ' and account_id=:account_id ';
            $arrParams[':account_id'] = User::getAccountId();
        }
        //需要保存的字段
        foreach ($arrSaveCol as $strCol) {
            $strSql .= "{$strCol}=:{$strCol},";
            $arrParams[":{$strCol}"] = $arrParam[$strCol];
        }
        $strSql = trim($strSql, ',');
        //sql
        $strSql = "update task set {$strSql},update_date=now() where id=:id and project_id=:project_id {$strWhere}";
        //exec
        $intRet = $this->objDB->setMainTable('task')->update($strSql, $arrParams);
        //返回
        return $intRet == 1 ? true : false;
    }

    // -------------------------------------- doneRequireInfo -------------------------------------- //

    /**
     * 编辑需求
     */
    public function doneRequireInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkDoneRequireInfo($arrParam, $arrSaveCol);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->doneRequire($arrParam, $arrSaveCol);
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
    protected function checkDoneRequireInfo(&$arrParam, &$arrSaveCol) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        $arrSaveCol = $this->getSaveColDone();
        if (empty($arrSaveCol)) {
            return '获取保存字段失败';
        }
        $arrParam['status'] = '03';
        $arrErrMsg = $this->objValidPostData->check($arrParam, array_merge(['id', 'project_id'], $arrSaveCol), $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
    }

    /**
     * 编辑需求
     */
    protected function doneRequire($arrParam, $arrSaveCol) {
        $strSql = '';
        $arrParams = [
            ':id' => $arrParam['id'],
            ':project_id' => $arrParam['project_id']
        ];
        //需要保存的字段
        foreach ($arrSaveCol as $strCol) {
            $strSql .= "{$strCol}=:{$strCol},";
            $arrParams[":{$strCol}"] = $arrParam[$strCol];
        }
        $strSql = trim($strSql, ',');
        //sql
        $strSql = "update task set {$strSql},update_date=now() where id=:id and project_id=:project_id";
        //exec
        $intRet = $this->objDB->setMainTable('task')->update($strSql, $arrParams);
        //返回
        return $intRet == 1 ? true : false;
    }

    // -------------------------------------- deleteRequireInfo -------------------------------------- //

    /**
     * 作废需求
     */
    public function deleteRequireInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkDeleteRequireInfo($arrParam);
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
    }

    /**
     * 作废需求
     */
    protected function deleteRequire($arrParam) {
        //param
        $arrParams = [
            ':id' => $arrParam['id'],
            ':project_id' => $arrParam['project_id'],
            ':status' => '00'
        ];
        //sql
        $strSql = "update task set status=:status,update_date=now() where id=:id and project_id=:project_id";
        $intRet = $this->objDB->setMainTable('task')->update($strSql, $arrParams);
        //返回
        return $intRet == 1 ? true : false;
    }

    // -------------------------------------- allotRequireInfo -------------------------------------- //

    /**
     * 分配需求
     */
    public function allotRequireInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkAllotRequireInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->allotRequire($arrParam);
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
    protected function checkAllotRequireInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRulesAllot;
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['project_id', 'need_done_date', 'account_id', 'task_id'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
        $arrTaskId = [];
        foreach (explode(',', $arrParam['task_id']) as $strTmp) {
            if (checkFormat($strTmp, Config::get('const.ValidFormat.FORMAT_POSINT'))) {
                $arrTaskId[] = $strTmp;
            }
        }
        if (empty($arrTaskId)) {
            return '请选择需要分配的需求';
        }
        $arrParam['task_id'] = $arrTaskId;
    }

    /**
     * 分配需求
     */
    protected function allotRequire($arrParam) {
        //param
        $arrParams = [
            ':project_id' => $arrParam['project_id'],
            ':need_done_date' => $arrParam['need_done_date'],
            ':account_id' => $arrParam['account_id'],
            ':status' => '02',
            ':old_status' => '01'
        ];
        $strWhere = '';
        foreach ($arrParam['task_id'] as $strTaskId) {
            $arrParams[":task_id{$strTaskId}"] = $strTaskId;
            $strWhere .= ":task_id{$strTaskId},";
        }
        $strWhere = trim($strWhere, ',');
        //sql
        $strSql = "update task set status=:status,account_id=:account_id,need_done_date=:need_done_date,update_date=now() where id in ({$strWhere}) and project_id=:project_id and status=:old_status";
        $intRet = $this->objDB->setMainTable('task')->update($strSql, $arrParams, true);
        //返回
        return $intRet > 0 ? true : false;
    }

    // -------------------------------------- reallotRequireInfo -------------------------------------- //

    /**
     * 重新分配需求
     */
    public function reallotRequireInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkReAllotRequireInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->reallotRequire($arrParam);
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
    protected function checkReAllotRequireInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRulesAllot;
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['project_id', 'account_id', 'task_id'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
        $arrTaskId = [];
        foreach (explode(',', $arrParam['task_id']) as $strTmp) {
            if (checkFormat($strTmp, Config::get('const.ValidFormat.FORMAT_POSINT'))) {
                $arrTaskId[] = $strTmp;
            }
        }
        if (empty($arrTaskId)) {
            return '请选择需要重新分配的需求';
        }
        $arrParam['task_id'] = $arrTaskId;
    }

    /**
     * 重新分配需求
     */
    protected function reallotRequire($arrParam) {
        //param
        $arrParams = [
            ':project_id' => $arrParam['project_id'],
            ':account_id' => $arrParam['account_id'],
            ':status' => '02'
        ];
        $strWhere = '';
        foreach ($arrParam['task_id'] as $strTaskId) {
            $arrParams[":task_id{$strTaskId}"] = $strTaskId;
            $strWhere .= ":task_id{$strTaskId},";
        }
        $strWhere = trim($strWhere, ',');
        //sql
        $strSql = "update task set account_id=:account_id,update_date=now() where id in ({$strWhere}) and project_id=:project_id and status=:status";
        $intRet = $this->objDB->setMainTable('task')->update($strSql, $arrParams, true);
        //返回
        return $intRet > 0 ? true : false;
    }

    // -------------------------------------- validator -------------------------------------- //
    // -------------------------------------- common -------------------------------------- //

    /**
     * 获取编辑需求时的字段
     */
    protected function getSaveCol() {
        //1.获取需求信息
        $arrRequireInfo = $this->getRequireInfo(Request::getAllParam());
        if (empty($arrRequireInfo)) {
            return [];
        }

        //2.根据角色与状态获取字段
        $strRole = User::getAccountRoleName();
        $strStatus = $arrRequireInfo['status'];
        $arrCol = [];
        switch ($strRole) {
            case 'admin':
            case 'manager':
                switch ($strStatus) {
                    case '01':
                        $arrCol = ['xingzhi', 'task_name', 'module_id', 'need_memo', 'need_attach'];
                        break;
                    case '02':
                    case '03':
                        $arrCol = ['xingzhi', 'task_name', 'module_id', 'need_memo', 'need_attach', 'page_enter', 'dev_memo', 'need_tip', 'change_file', 'sql_attach', 'other_attach'];
                        break;
                    case '04':
                        $arrCol = ['xingzhi', 'task_name', 'module_id', 'need_memo', 'need_attach', 'page_enter', 'dev_memo', 'need_tip', 'change_file', 'sql_attach', 'other_attach'];
                        if ($arrRequireInfo['round'] != 0) {
                            $arrCol[] = 'change_file' . $arrRequireInfo['round'];
                        }
                        break;
                    default:
                        $arrCol = [];
                        break;
                }
                break;
            case 'product':
                switch ($strStatus) {
                    case '01':
                        $arrCol = ['xingzhi', 'task_name', 'module_id', 'need_memo', 'need_attach'];
                        break;
                    default:
                        $arrCol = [];
                        break;
                }
                break;
            case 'devloper':
                switch ($strStatus) {
                    case '02':
                        $arrCol = ['page_enter', 'dev_memo', 'need_tip', 'change_file', 'sql_attach', 'other_attach'];
                        break;
                    case '04':
                        if ($arrRequireInfo['round'] != 0) {
                            $arrCol[] = 'change_file' . $arrRequireInfo['round'];
                        }
                        break;
                    default:
                        $arrCol = [];
                        break;
                }
                break;
            default :
                $arrCol = [];
                break;
        }

        //3.返回
        return $arrCol;
    }

    /**
     * 获取完成需求时的字段
     */
    protected function getSaveColDone() {
        //1.获取需求信息
        $arrRequireInfo = $this->getRequireInfo(Request::getAllParam());
        if (empty($arrRequireInfo)) {
            return [];
        }

        $arrCol = ['status'];
        if ($arrRequireInfo['is_timeout'] == 1) {
            $arrCol[] = 'dev_dealy_reason';
        }

        return $arrCol;
    }

}
