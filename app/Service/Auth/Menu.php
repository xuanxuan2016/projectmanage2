<?php

namespace App\Service\Auth;

use Framework\Facade\App;
use Framework\Facade\Request;
use Framework\Service\Database\DB;
use Framework\Facade\User as UserFacade;

/**
 * 菜单与功能点
 */
class Menu {

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

    /**
     * 获取缓存路径
     */
    protected function getFilePath($strAccountId, $strType) {
        return App::make('path.storage') . '/cache/auth/' . $strType . '_' . $strAccountId . '.log';
    }

    /**
     * 清除临时文件
     * 1.用户登录
     * 2.用户账号角色修改
     * @param string $strAccountId 角色id
     * @param string $strType 缓存类别
     */
    public function delCacheFileByAccountId($strAccountId, $strType) {
        $strFilePath = $this->getFilePath($strAccountId, $strType);
        if (file_exists($strFilePath)) {
            unlink($strFilePath);
        }
    }

    /**
     * 清除临时文件
     * 1.角色权限点修改
     * @param string $strRoleId 角色id
     */
    public function delCacheFileByRoleId($strRoleId) {
        $strSql = 'select id from account where role_id=:role_id and status=:status';
        $arrParams = [
            ':role_id' => $strRoleId,
            ':status' => '01'
        ];
        $arrAccount = $this->objDB->setMainTable('account')->select($strSql, $arrParams);
        foreach ($arrAccount as $arrAccountTmp) {
            $this->delCacheFileByAccountId($arrAccountTmp['id'], 'auth');
        }
    }

    /**
     * 清除临时文件
     * 1.项目关联人员修改
     * @param string $strProjectId 项目id
     */
    public function delCacheFileByProjectId($strProjectId) {
        $strSql = 'select account_id from projectperson where project_id=:project_id and status=:status';
        $arrParams = [
            ':project_id' => $strProjectId,
            ':status' => '01'
        ];
        $arrAccount = $this->objDB->setMainTable('projectperson')->select($strSql, $arrParams);
        foreach ($arrAccount as $arrAccountTmp) {
            $this->delCacheFileByAccountId($arrAccountTmp['account_id'], 'project');
        }
    }

    /**
     * 获取功能点
     */
    protected function getAuth() {
        $strFilePath = $this->getFilePath(UserFacade::getAccountId(), 'auth');
        if (file_exists($strFilePath)) {
            //从缓存文件获取
            return json_decode(file_get_contents($strFilePath), true);
        } else {
            //从数据库获取
            $strSql = 'select a.cname,a.code,a.icode,a.itype,a.url,a.icon
                        from einterface a 
                        join einterfacerole b on a.id=b.auth_id
                        where b.role_id=:role_id and a.status=:status and b.status=:status
                        order by SUBSTR(a.code,1,2),a.list_index,a.code';
            $arrParams = [
                ':role_id' => UserFacade::getAccountRole(),
                ':status' => '01'
            ];
            $arrAuth = $this->objDB->setMainTable('einterface')->select($strSql, $arrParams);
            //写入文件
            file_put_contents($strFilePath, json_encode($arrAuth), LOCK_EX);
            //返回数据
            return $arrAuth;
        }
    }

    /**
     * 获取可用项目
     */
    protected function getProject() {
        $strFilePath = $this->getFilePath(UserFacade::getAccountId(), 'project');
        if (file_exists($strFilePath)) {
            //从缓存文件获取
            return json_decode(file_get_contents($strFilePath), true);
        } else {
            //从数据库获取
            $strSql = 'select b.id,b.cname,a.project_id
                    from projectperson a
                            join project b on a.project_id=b.id
                    where a.account_id=:account_id and a.status=:status and b.status=:status
                    order by b.id';
            $arrParams = [
                ':account_id' => UserFacade::getAccountId(),
                ':status' => '01'
            ];
            $arrProject = $this->objDB->setMainTable('projectperson')->select($strSql, $arrParams);
            //写入文件
            file_put_contents($strFilePath, json_encode($arrProject), LOCK_EX);
            //返回数据
            return $arrProject;
        }
    }

    /**
     * 获取uri
     */
    protected function getUri() {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * 获取菜单
     */
    public function getMenu() {
        //1.获取功能点
        $arrAuth = $this->getAuth();
        //2.生成菜单
        $strMenu = '';
        $arrRoute = [];
        //2.1.一级菜单
        foreach ($this->getSubMenu($arrAuth, 0) as $arrLevel0Tmp) {
            $strMenu .= "<el-submenu index='{$arrLevel0Tmp['code']}'>
                        <template slot='title'>
                            <i class='{$arrLevel0Tmp['icon']}'></i>
                            <span>{$arrLevel0Tmp['cname']}</span>
                        </template>";
            //2.2.二级菜单
            foreach ($this->getSubMenu($arrAuth, 1, $arrLevel0Tmp['code']) as $arrLevel1Tmp) {
                $strMenu .= "<el-menu-item index='{$arrLevel1Tmp['url']}'>{$arrLevel1Tmp['cname']}</el-menu-item>";
                //菜单路径
                if ($arrLevel1Tmp['url'] == $this->getUri()) {
                    $arrRoute = [$arrLevel0Tmp['code']];
                }
            }
            $strMenu .= "</el-submenu>";
        }
        //2.3.替换项目菜单
        $strMenu = $this->replaceProjectMenu($strMenu, $arrRoute);
        //3.最终菜单
        $strOpenRoute = json_encode($arrRoute);
        $strMenu = "<el-menu
                    :default-openeds='{$strOpenRoute}'
                    default-active='{$this->getUri()}'
                    background-color='#545c64'
                    text-color='#fff'
                    :unique-opened='true'
                    :collapse='menu_collapse'
                    :collapse-transition='false'
                    active-text-color='#ffd04b' 
                    @select='selectMenu'>{$strMenu}
                </el-menu>";
        //4.返回
        return $strMenu;
    }

    /**
     * 获取子菜单
     */
    protected function getSubMenu($arrAuth, $intIType, $strPrefix = '') {
        return array_values(array_filter($arrAuth, function($value) use($intIType, $strPrefix) {
                    return $value['itype'] == $intIType && ($strPrefix == '' ? true : strpos($value['code'], $strPrefix) === 0);
                }));
    }

    /**
     * 替换项目菜单
     */
    protected function replaceProjectMenu($strMenu, &$arrRoute) {
        $strProjectMenu = '';
        $strReplace = "<el-menu-item index='/web/task/require'>需求</el-menu-item><el-menu-item index='/web/task/qa'>送测</el-menu-item>";
        $arrProject = $this->getProject();
        //循环项目
        foreach ($arrProject as $arrProjectTmp) {
            $strProjectMenu .= "<el-submenu index='{$arrProjectTmp['cname']}'>
                            <template slot='title'>{$arrProjectTmp['cname']}</template>
                            <el-menu-item index='/web/task/require?project_id={$arrProjectTmp['id']}'>需求</el-menu-item>
                            <el-menu-item index='/web/task/qa?project_id={$arrProjectTmp['id']}'>送测</el-menu-item>
                        </el-submenu>";
            //菜单路径
            if (in_array($this->getUri(), ["/web/task/require?project_id={$arrProjectTmp['id']}", "/web/task/qa?project_id={$arrProjectTmp['id']}"])) {
                $arrRoute = ['02', $arrProjectTmp['cname']];
            }
        }
        //替换menu
        return str_replace($strReplace, $strProjectMenu, $strMenu);
    }

    /**
     * 获取按钮权限
     */
    public function getAuthButton($strPage) {
        //1.获取功能点
        $arrAuth = $this->getAuth();
        //2.获取页面code
        $strPageCode = array_values(array_filter($arrAuth, function($arrAuthTmp) use($strPage) {
                            return $arrAuthTmp['icode'] == $strPage;
                        }))[0]['code'];
        //3.根据页面code获取按钮
        $arrAuthButton = array_values(array_filter($arrAuth, function($arrAuthTmp) use($strPageCode) {
                    return strpos($arrAuthTmp['code'], $strPageCode) === 0 && $arrAuthTmp['itype'] == 2;
                }));
        //4.行转列
        $arrTmp = [];
        foreach ($arrAuthButton as $arrAuthButtonTmp) {
            $arrTmp[$arrAuthButtonTmp['icode']] = 1;
        }
        return $arrTmp;
    }

    /**
     * 检查按钮权限
     */
    public function checkAuthButton($strAuthCode) {
        //1.获取功能点
        $arrAuth = $this->getAuth();
        //2.检查权限
        return in_array($strAuthCode, array_column($arrAuth, 'icode'));
    }

    /**
     * 检查项目权限
     */
    public function checkProject() {
        //1.获取功能点
        $arrProject = $this->getProject();
        //2.检查权限
        return in_array(Request::getParam('project_id'), array_column($arrProject, 'project_id'));
    }

    /**
     * 获取项目名称
     */
    public function getProjectName() {
        //1.获取功能点
        $arrProject = $this->getProject();
        //2.检查权限
        $arrCurProject = array_values(array_filter($arrProject, function($value) {
                    return $value['project_id'] == Request::getParam('project_id');
                }));
        return !empty($arrCurProject) ? $arrCurProject[0]['cname'] : '';
    }

}
