<?php

namespace App\Http\Model\Web\Auth;

use Framework\Facade\Des;
use Framework\Facade\User;
use Framework\Facade\Request;
use Framework\Service\Database\DB;
use Framework\Service\Validation\ValidDBData;
use Framework\Service\Validation\ValidPostData;

class PasswordModel {

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
        'new_pwd1' => [
            'required' => ['value' => true, 'err_msg' => '请输入新密码']
        ],
        'new_pwd2' => [
            'required' => ['value' => true, 'err_msg' => '请输入新密码']
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

    // -------------------------------------- savePasswordInfo -------------------------------------- //

    /**
     * 保存密码信息
     */
    public function savePasswordInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkSavePasswordInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->savePassword($arrParam);
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
    protected function checkSavePasswordInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['new_pwd1', 'new_pwd2'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查    
        if ($arrParam['new_pwd1'] != $arrParam['new_pwd2']) {
            return '2次输入的密码不一致';
        }
        //5.其它参数
        $arrParam['password'] = Des::passwordHash($arrParam['new_pwd1']);
    }

    /**
     * 保存密码
     */
    protected function savePassword($arrParam) {
        //param
        $arrParams = [
            ':id' => User::getAccountId(),
            ':password' => $arrParam['password']
        ];
        //sql
        $strSql = "update account set password=:password,update_date=now() where id=:id";
        //exec
        $intRet = $this->objDB->setMainTable('password')->update($strSql, $arrParams);
        //返回
        return $intRet == 1 ? true : false;
    }

    // -------------------------------------- validator -------------------------------------- //
}
