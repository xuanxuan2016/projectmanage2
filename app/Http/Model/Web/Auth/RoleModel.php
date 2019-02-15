<?php

namespace App\Http\Model\Web\Auth;

use App\Facade\Menu;
use Framework\Facade\Request;
use Framework\Service\Database\DB;
use Framework\Service\Validation\ValidDBData;
use Framework\Service\Validation\ValidPostData;

class RoleModel {

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
            'required' => ['value' => true, 'err_msg' => '请输入角色名称']
        ],
        'status' => [
            'optional' => ['value' => ['01', '06'], 'err_msg' => '请设置角色是否有效']
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
        $arrRoleList = $this->getRoleList($arrParam);
        //4.结果返回
        $arrData = $arrRoleList;
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
            $strWhereSql .= ' and a.status=:status';
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
    protected function getRoleList($arrParam) {
        //分页
        $intPageSize = $arrParam['page_size'];
        $intStart = ($arrParam['page_index'] - 1) * $intPageSize;

        //查询
        $strSql = "select a.id,a.cname,a.status
                    from role a
                    where 1=1 {$arrParam['where']['sql']}";
        $arrParams = $arrParam['where']['param'];
        $intTotal = 0;
        $arrRoleList = $this->objDB->setMainTable('role')->selectPage($strSql, $intStart, $intPageSize, $intTotal, $arrParams, true);

        //返回
        return [
            'list' => $arrRoleList,
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
        $arrData['interface'] = $this->getInterfaceInfo();
        //4.结果返回        
        return true;
    }

    /**
     * 获取权限点
     */
    protected function getInterfaceInfo() {
        //查询
        $strSql = "select id,cname,code,itype from einterface where status=:status order by code";
        $arrParams[':status'] = '01';
        $arrInterfaceInfo = $this->objDB->setMainTable('role')->select($strSql, $arrParams);

        //获取权限的树状结构
        $arrRoot[] = [
            'id' => 0,
            'label' => '权限',
            'children' => $this->getChildren($arrInterfaceInfo, 0, '0')
        ];

        //返回
        return $arrRoot;
    }

    /**
     * 将权限点处理为树状结构
     */
    protected function getChildren($arrInterfaceInfo, $strIType, $strCode) {
        $arrChildren = [];
        //根据itype获取节点
        $arrLevel = array_values(array_filter($arrInterfaceInfo, function($value) use($strIType, $strCode) {
                    return $value['itype'] == $strIType && strpos($value['code'], $strCode) === 0;
                }));
        //遍历获取到的节点
        foreach ($arrLevel as $arrLevelTmp) {
            $arrChildren[] = [
                'id' => $arrLevelTmp['id'],
                'label' => $arrLevelTmp['cname'],
                'children' => $this->getChildren($arrInterfaceInfo, $arrLevelTmp['itype'] + 1, $arrLevelTmp['code'])
            ];
        }
        //返回
        return $arrChildren;
    }

    // -------------------------------------- loadRoleInfo -------------------------------------- //

    /**
     * 加载账号信息
     */
    public function loadRoleInfo(&$strErrMsg, &$arrData) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkLoadRoleInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $arrRoleInfo = $this->getRoleInfo($arrParam);
        $arrRoleInfo['auth'] = $this->getAuthInfo($arrParam);
        //4.结果返回
        $arrData['info'] = $arrRoleInfo;
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkLoadRoleInfo(&$arrParam) {
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
    protected function getRoleInfo($arrParam) {
        //查询
        $strSql = "select id,cname,status from role where id=:id";
        $arrParams[':id'] = $arrParam['id'];
        $arrRoleInfo = $this->objDB->setMainTable('role')->select($strSql, $arrParams);

        //返回
        return $arrRoleInfo[0];
    }

    /**
     * 获取角色的权限数据
     */
    protected function getAuthInfo($arrParam) {
        //查询
        $strSql = "select a.auth_id ,b.code
                    from einterfacerole a
                        join einterface b on a.auth_id=b.id
                    where a.role_id=:id and a.status=:status";
        $arrParams[':id'] = $arrParam['id'];
        $arrParams[':status'] = '01';
        $arrAuthInfo = $this->objDB->setMainTable('einterfacerole')->select($strSql, $arrParams);
        //获取叶子节点
        $arrLeaf = [];
        foreach ($arrAuthInfo as $arrAuthTmp) {
            $strCode = $arrAuthTmp['code'];
            if (empty(array_filter($arrAuthInfo, function($value) use($strCode) {
                                return $strCode != $value['code'] && strpos($value['code'], $strCode) === 0;
                            }))) {
                $arrLeaf[] = $arrAuthTmp['auth_id'];
            }
        }
        //返回
        return $arrLeaf;
    }

    // -------------------------------------- saveRoleInfo -------------------------------------- //

    /**
     * 加载账号信息
     */
    public function saveRoleInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkSaveRoleInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->updateRoleInfo($arrParam);
        if ($blnRet && !empty($arrParam['id'])) {
            //移除cache文件
            Menu::delCacheFileByRoleId($arrParam['id']);
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
    protected function checkSaveRoleInfo(&$arrParam) {
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
        $arrAuth = json_decode($arrParam['auth'], true);
        if (in_array('0', $arrAuth)) {
            unset($arrAuth[array_search('0', $arrAuth)]);
        }
        if (empty($arrAuth)) {
            return '请设置权限点';
        }
        //5.其它参数
        $arrParam['auth'] = array_values($arrAuth);
    }

    /**
     * 保存数据
     */
    protected function updateRoleInfo($arrParam) {
        //param
        $arrParams = [
            ':id' => $arrParam['id'],
            ':cname' => $arrParam['cname'],
            ':status' => $arrParam['status']
        ];
        //开启事务
        $this->objDB->setMainTable('role')->beginTran();
        //1.role
        if ($arrParams[':id'] == 0) {
            unset($arrParams[':id']);
            $strSql = 'insert into role(cname,status) values(:cname,:status)';
            //exec
            $intRoleId = $this->objDB->setMainTable('role')->insert($strSql, $arrParams);
            if ($intRoleId < 0) {
                $this->objDB->setMainTable('role')->rollbackTran();
                return false;
            }
        } else {
            $strSql = "update role set cname=:cname,status=:status,update_date=now() where id=:id";
            //exec
            $intRet = $this->objDB->setMainTable('role')->update($strSql, $arrParams);
            if ($intRet != 1) {
                $this->objDB->setMainTable('role')->rollbackTran();
                return false;
            }
        }

        //2.einterfacerole
        //2.1.删除
        $strSql = 'update einterfacerole set status=:status,update_date=now() where role_id=:role_id';
        $arrParams = [
            ':role_id' => $arrParam['id'],
            ':status' => '06'
        ];
        $intRet = $this->objDB->setMainTable('role')->update($strSql, $arrParams, true);
        if ($intRet < 0) {
            $this->objDB->setMainTable('role')->rollbackTran();
            return false;
        }

        //2.2.插入
        $arrParams = [
            ':role_id' => $arrParam['id'] == 0 ? $intRoleId : $arrParam['id'],
            ':status' => '01'
        ];
        $strAuth = '';
        for ($i = 0, $j = count($arrParam['auth']); $i < $j; $i++) {
            if (is_numeric($arrParam['auth'][$i])) {
                $strAuth .= ":authid{$i},";
                $arrParams[":authid{$i}"] = $arrParam['auth'][$i];
            }
        }
        $strAuth = trim($strAuth, ',');
        $strSql = "insert into einterfacerole(role_id,auth_id) select :role_id,id from einterface where status=:status and id in ({$strAuth})";
        $intRet = $this->objDB->setMainTable('role')->insert($strSql, $arrParams, false);
        if ($intRet < 1) {
            $this->objDB->setMainTable('role')->rollbackTran();
            return false;
        }
        //提交事务
        $this->objDB->setMainTable('role')->commitTran();
        //返回
        return true;
    }

    // -------------------------------------- validator -------------------------------------- //
}
