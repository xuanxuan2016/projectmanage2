<?php

namespace App\Http\Model\Web\Common;

use App\Facade\Menu;
use Framework\Facade\Des;
use Framework\Facade\Request;
use Framework\Service\Database\DB;

class LoginModel {

    /**
     * 数据实例
     */
    protected $objDB;

    /**
     * 构造方法
     */
    public function __construct(DB $objDB) {
        $this->objDB = $objDB;
    }

    // -------------------------------------- logOut -------------------------------------- //
    public function logOut(&$strErrMsg) {
        $strErrMsg = '';
        Request::delCookie('Dev2LoginInfo');
        return true;
    }

    // -------------------------------------- login -------------------------------------- //

    /**
     * 登录
     */
    public function login(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkLogin($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $arrLoginInfo = $this->checkAccount($arrParam);
        //4.结果返回
        if (!empty($arrLoginInfo)) {
            //移除权限cache文件
            Menu::delCacheFileByAccountId($arrLoginInfo['account_id'], 'auth');
            Menu::delCacheFileByAccountId($arrLoginInfo['account_id'], 'project');
            //cookie记录
            Request::setCookie('Dev2LoginInfo', json_encode($arrLoginInfo), time() + 5 * 24 * 3600);
            //返回
            return true;
        } else {
            $strErrMsg = '账号或密码错误';
            return false;
        }
    }

    /**
     * 检查账号信息
     */
    protected function checkAccount($arrParam) {
        //1.获取账号信息
        $strSql = 'select a.id,a.cname,a.password,a.role_id,b.cname role_name 
                    from account a
                        join role b on a.role_id=b.id
                    where a.username=:username and a.status=:status';
        $arrParams = [
            ':username' => $arrParam['username'],
            ':status' => '01'
        ];
        $arrAccount = $this->objDB->setMainTable('account')->select($strSql, $arrParams);
        //2.检查账号信息
        if (empty($arrAccount) || !Des::passwordVerify($arrParam['password'], $arrAccount[0]['password'])) {
            return [];
        } else {
            return [
                'account_id' => $arrAccount[0]['id'],
                'account_name' => $arrAccount[0]['cname'],
                'account_role' => $arrAccount[0]['role_id'],
                'account_role_name' => $arrAccount[0]['role_name']
            ];
        }
    }

    /**
     * 参数检查
     */
    protected function checkLogin(&$arrParam) {
        //用户名密码不能为空
        if (Request::getParam('username') === '' || Request::getParam('password') === '') {
            return '账号与密码不能为空';
        }
        //获取页面参数
        $arrParam['username'] = trim(Request::getParam('username'));
        $arrParam['password'] = trim(Request::getParam('password'));
    }

}
