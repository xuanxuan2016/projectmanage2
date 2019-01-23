<?php

namespace App\Http\Model\Web\Auth;

use Framework\Facade\Des;
use Framework\Facade\Request;
use Framework\Service\Database\DB;
use Framework\Service\Validation\ValidDBData;
use Framework\Service\Validation\ValidPostData;

class AccountModel {

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
            'required' => ['value' => true, 'err_msg' => '请输入用户昵称']
        ],
        'username' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请输入账号名称']
        ],
        'password' => [
            'trim' => ['value' => true],
            'required' => ['value' => true, 'err_msg' => '请输入账号密码']
        ],
        'status' => [
            'optional' => ['value' => ['01', '06'], 'err_msg' => '请设置账号是否有效']
        ],
        'role_id' => [
            'type' => ['value' => 'posint', 'err_msg' => '请选择账号角色']
        ],
        'is_can_search' => [
            'optional' => ['value' => ['0', '1'], 'err_msg' => '请设置账号可被查询']
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
        $arrAccountList = $this->getAccountList($arrParam);
        //4.结果返回
        $arrData = $arrAccountList;
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
        //is_can_search
        if (in_array($arrSearchParam['is_can_search'], ['1', '0'])) {
            $strWhereSql.=' and a.is_can_search=:is_can_search';
            $arrWhereParam[':is_can_search'] = $arrSearchParam['is_can_search'];
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
    protected function getAccountList($arrParam) {
        //分页
        $intPageSize = $arrParam['page_size'];
        $intStart = ($arrParam['page_index'] - 1) * $intPageSize;

        //查询
        $strSql = "select a.id,a.cname,a.username,a.is_can_search,a.role_id,b.cname 'role_id_tran',a.status
                    from account a
                        join role b on a.role_id=b.id
                    where 1=1 {$arrParam['where']['sql']}";
        $arrParams = $arrParam['where']['param'];
        $intTotal = 0;
        $arrAccountList = $this->objDB->setMainTable('account')->selectPage($strSql, $intStart, $intPageSize, $intTotal, $arrParams, true);

        //返回
        return [
            'list' => $arrAccountList,
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
        $arrData['roles'] = $this->getRoleInfo();
        //4.结果返回        
        return true;
    }

    /**
     * 获取角色
     */
    protected function getRoleInfo() {
        //查询
        $strSql = "select id,cname from role where status=:status";
        $arrParams[':status'] = '01';
        $arrRoleInfo = $this->objDB->setMainTable('role')->select($strSql, $arrParams);

        //返回
        return $arrRoleInfo;
    }

    // -------------------------------------- loadAccountInfo -------------------------------------- //

    /**
     * 加载账号信息
     */
    public function loadAccountInfo(&$strErrMsg, &$arrData) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkLoadAccountInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $arrAccountInfo = $this->getAccountInfo($arrParam);
        //4.结果返回
        $arrData['info'] = $arrAccountInfo;
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkLoadAccountInfo(&$arrParam) {
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
    protected function getAccountInfo($arrParam) {
        //查询
        $strSql = "select id,cname,username,role_id,is_can_search,status from account where id=:id";
        $arrParams[':id'] = $arrParam['id'];
        $arrAccountInfo = $this->objDB->setMainTable('account')->select($strSql, $arrParams);

        //返回
        return $arrAccountInfo[0];
    }

    // -------------------------------------- saveAccountInfo -------------------------------------- //

    /**
     * 加载账号信息
     */
    public function saveAccountInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkSaveAccountInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->updateAccountInfo($arrParam);
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
    protected function checkSaveAccountInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        if ($arrParam['id'] == '0') {
            unset($arrRules['id']);
        } else {
            $arrRules['password']['required']['value'] = false;
        }
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['id', 'cname', 'username', 'password', 'status', 'role_id', 'is_can_search'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //3.1.需要检查的字段
        $arrCheckCol = ['id', 'cname', 'username', 'password', 'status', 'role_id', 'is_can_search'];
        //3.2.可为空的字段
        $arrCanEmpty = [];
        if (!empty($arrParam['id'])) {
            $arrCanEmpty[] = 'password';
        }
        //3.3.check
        $arrErrMsg = $this->objValidDBData->check('account', $arrParam, $arrCheckCol);
        if (!empty($arrErrMsg)) {
            $strErrmsg = '';
            foreach ($arrErrMsg as $arrTmp) {
                if ($arrTmp['err_code'] == '01' && in_array($arrTmp['col_name'], $arrCanEmpty)) {
                    //排除可为空的字段
                    continue;
                }
                $strErrmsg .= $arrTmp['col_name'] . ':' . $arrTmp['err_msg'] . ';';
            }
            if (!empty($strErrmsg)) {
                return $strErrmsg;
            }
        }

        //4.业务检查    
        //5.其它参数
        $arrParam['password'] = Des::passwordHash($arrParam['password']);
    }

    /**
     * 保存数据
     */
    protected function updateAccountInfo($arrParam) {
        //param
        $arrParams = [
            ':id' => $arrParam['id'],
            ':cname' => $arrParam['cname'],
            ':username' => $arrParam['username'],
            ':password' => $arrParam['password'],
            ':role_id' => $arrParam['role_id'],
            ':is_can_search' => $arrParam['is_can_search'],
            ':status' => $arrParam['status']
        ];
        //sql
        if ($arrParams[':id'] == 0) {
            unset($arrParams[':id']);
            $strSql = 'insert into account(cname,username,password,role_id,is_can_search,status) values(:cname,:username,:password,:role_id,:is_can_search,:status)';
            //exec
            $intRet = $this->objDB->setMainTable('account')->insert($strSql, $arrParams, false);
        } else {
            $strSql = "update account set cname=:cname,username=:username,password=case when :password!='' then :password else password end,"
                    . "role_id=:role_id,status=:status,is_can_search=:is_can_search,update_date=now() "
                    . "where id=:id";
            //exec
            $intRet = $this->objDB->setMainTable('account')->update($strSql, $arrParams);
        }
        //返回
        return $intRet == 1 ? true : false;
    }

    // -------------------------------------- validator -------------------------------------- //
}
