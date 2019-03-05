<?php

namespace App\Http\Model\Web\Auth;

use Framework\Facade\Request;
use Framework\Service\Database\DB;
use Framework\Service\Validation\ValidDBData;
use Framework\Service\Validation\ValidPostData;

class InterfaceModel {

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
            'required' => ['value' => true, 'err_msg' => '请输入权限名称']
        ],
        'code' => [
            'required' => ['value' => true, 'err_msg' => '请输入权限code']
        ],
        'icode' => [
            'required' => ['value' => true, 'err_msg' => '请输入权限key']
        ],
        'itype' => [
            'optional' => ['value' => ['0', '1', '2'], 'err_msg' => '请设置权限类别']
        ],
        'url' => [
            'required' => ['value' => true, 'err_msg' => '请输入页面地址']
        ],
        'icon' => [
            'required' => ['value' => true, 'err_msg' => '请输入页面图标']
        ],
        'status' => [
            'optional' => ['value' => ['01', '06'], 'err_msg' => '请设置权限是否有效']
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
        $arrInterfaceList = $this->getInterfaceList($arrParam);
        //4.结果返回
        $arrData = $arrInterfaceList;
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
        //is_can_search
        if (in_array($arrSearchParam['itype'], ['0', '1', '2'])) {
            $strWhereSql .= ' and a.itype=:itype';
            $arrWhereParam[':itype'] = $arrSearchParam['itype'];
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
    protected function getInterfaceList($arrParam) {
        //分页
        $intPageSize = $arrParam['page_size'];
        $intStart = ($arrParam['page_index'] - 1) * $intPageSize;

        //查询
        $strSql = "select id,cname,code,icode,itype,url,icon,status
                    from einterface a
                    where 1=1 {$arrParam['where']['sql']}
                    order by a.code
                    ";
        $arrParams = $arrParam['where']['param'];
        $intTotal = 0;
        $arrInterfaceList = $this->objDB->setMainTable('einterface')->selectPage($strSql, $intStart, $intPageSize, $intTotal, $arrParams, true);

        //返回
        return [
            'list' => $arrInterfaceList,
            'total' => $intTotal
        ];
    }

    // -------------------------------------- loadInterfaceInfo -------------------------------------- //

    /**
     * 加载账号信息
     */
    public function loadInterfaceInfo(&$strErrMsg, &$arrData) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkLoadInterfaceInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $arrInterfaceInfo = $this->getInterfaceInfo($arrParam);
        //4.结果返回
        $arrData['info'] = $arrInterfaceInfo;
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkLoadInterfaceInfo(&$arrParam) {
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
    protected function getInterfaceInfo($arrParam) {
        //查询
        $strSql = "select id,cname,code,icode,itype,url,icon,status from einterface where id=:id";
        $arrParams[':id'] = $arrParam['id'];
        $arrInterfaceInfo = $this->objDB->setMainTable('einterface')->select($strSql, $arrParams);

        //返回
        return $arrInterfaceInfo[0];
    }

    // -------------------------------------- saveInterfaceInfo -------------------------------------- //

    /**
     * 加载账号信息
     */
    public function saveInterfaceInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkSaveInterfaceInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->updateInterfaceInfo($arrParam);
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
    protected function checkSaveInterfaceInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        if ($arrParam['id'] == '0') {
            unset($arrRules['id']);
        }
        switch ($arrParam['itype']) {
            case '0':
                $arrRules['url']['required']['value'] = false;
                $arrRules['icon']['required']['value'] = true;
                break;
            case '1':
                $arrRules['url']['required']['value'] = true;
                $arrRules['icon']['required']['value'] = false;
                break;
            case '2':
                $arrRules['url']['required']['value'] = false;
                $arrRules['icon']['required']['value'] = false;
                break;
        }
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['id', 'cname', 'code', 'icode', 'itype', 'url', 'icon', 'status'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查    
        //5.其它参数
    }

    /**
     * 保存数据
     */
    protected function updateInterfaceInfo($arrParam) {
        //param
        $arrParams = [
            ':id' => $arrParam['id'],
            ':cname' => $arrParam['cname'],
            ':code' => $arrParam['code'],
            ':icode' => $arrParam['icode'],
            ':itype' => $arrParam['itype'],
            ':url' => $arrParam['url'],
            ':icon' => $arrParam['icon'],
            ':status' => $arrParam['status']
        ];
        //sql
        if ($arrParams[':id'] == 0) {
            unset($arrParams[':id']);
            $strSql = 'insert into einterface(cname,code,icode,itype,url,icon,status) values(:cname,:code,:icode,:itype,:url,:icon,:status)';
            //exec
            $intRet = $this->objDB->setMainTable('einterface')->insert($strSql, $arrParams, false);
        } else {
            $strSql = "update einterface set cname=:cname,code=:code,icode=:icode,itype=:itype,"
                    . "url=:url,icon=:icon,status=:status,update_date=now() "
                    . "where id=:id";
            //exec
            $intRet = $this->objDB->setMainTable('einterface')->update($strSql, $arrParams);
        }
        //返回
        return $intRet == 1 ? true : false;
    }

    // -------------------------------------- validator -------------------------------------- //
}
