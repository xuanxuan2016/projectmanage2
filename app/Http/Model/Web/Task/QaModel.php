<?php

namespace App\Http\Model\Web\Task;

use Framework\Facade\App;
use Framework\Facade\File;
use Framework\Facade\Config;
use Framework\Facade\Request;
use Framework\Service\Database\DB;
use Framework\Service\Validation\ValidPostData;

class QaModel {

    /**
     * 数据实例
     */
    protected $objDB;

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
        ]
    ];

    /**
     * 构造方法
     */
    public function __construct(DB $objDB, ValidPostData $objValidPostData) {
        $this->objDB = $objDB;
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
        $arrQaList = $this->getQaList($arrParam);
        //4.结果返回
        $arrData = $arrQaList;
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
        //qa_name
        if (!empty($arrSearchParam['qa_name'])) {
            $strWhereSql .= ' and locate(:qa_name,a.qa_name)>0';
            $arrWhereParam[':qa_name'] = $arrSearchParam['qa_name'];
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
    protected function getQaList($arrParam) {
        //分页
        $intPageSize = $arrParam['page_size'];
        $intStart = ($arrParam['page_index'] - 1) * $intPageSize;

        //查询
        $strSql = "select a.id,a.qa_name,a.account_name,a.summary,a.round,a.status,a.qa_date,a.online_date
                    from qa a
                    where 1=1 and a.status in ('01','02') {$arrParam['where']['sql']}
                    order by a.create_date desc";
        $arrParams = $arrParam['where']['param'];
        $intTotal = 0;
        $arrQaList = $this->objDB->setMainTable('qa')->selectPage($strSql, $intStart, $intPageSize, $intTotal, $arrParams, true);

        //返回
        return [
            'list' => $arrQaList,
            'total' => $intTotal
        ];
    }

    // -------------------------------------- outputQaInfo -------------------------------------- //

    /**
     * 获取列表数据
     */
    public function outputQaInfo(&$strErrMsg, &$arrData) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkOutputQaInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $strAttachId = $this->outputQa($arrParam);
        if (empty($strAttachId)) {
            $strErrMsg = '导出失败';
            return false;
        }
        //4.结果返回
        $arrData['attach_id'] = $strAttachId;
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkOutputQaInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = [
            'project_id' => Request::getParam('project_id')
        ];

        //2.字段自定义配置检查
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['project_id'], $this->arrRules);
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
            $strWhereSql .= ' and a.needer_id=:needer_id';
            $arrWhereParam[':needer_id'] = $arrSearchParam['needer'];
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
     * 导出数据
     */
    protected function outputQa($arrParam) {
        //查询
        $strSql = "select a.task_name,b.type module_type,b.cname module_name,c.cname account_name,d.cname needer,a.status,a.xingzhi,a.create_date,a.send_date,a.need_done_date,a.done_date
                    from task a
                        join module b on a.module_id=b.id
                        left join account c on a.account_id=c.id
                        join account d on a.needer_id=d.id
                    where 1=1 {$arrParam['where']['sql']}
                    order by a.create_date desc";
        $arrParams = $arrParam['where']['param'];
        $intTotal = 0;
        $arrQaList = $this->objDB->setMainTable('task')->select($strSql, $arrParams);

        //翻译
        $arrModuleType = ['01' => '系统', '02' => '业务'];
        $arrXingZhi = ['01' => '确定', '02' => '待定'];
        $arrStatus = ['00' => '作废', '01' => '需求', '02' => '开发', '03' => '就绪', '04' => '送测', '05' => '上线'];
        foreach ($arrQaList as &$arrRow) {
            $arrRow['module_type'] = $arrModuleType[$arrRow['module_type']];
            $arrRow['xingzhi'] = $arrXingZhi[$arrRow['xingzhi']];
            $arrRow['status'] = $arrStatus[$arrRow['status']];
        }

        //生成下载文件
        $arrColumnMap = [
            'task_name' => ['cname' => '需求名称', 'is_output' => 1],
            'module_type' => ['cname' => '模块类型', 'is_output' => 1],
            'module_name' => ['cname' => '模块名称', 'is_output' => 1],
            'account_name' => ['cname' => '开发人员', 'is_output' => 1],
            'needer' => ['cname' => '提出人', 'is_output' => 1],
            'status' => ['cname' => '状态', 'is_output' => 1],
            'xingzhi' => ['cname' => '性质', 'is_output' => 1],
            'create_date' => ['cname' => '需求时间', 'is_output' => 1],
            'send_date' => ['cname' => '分配时间', 'is_output' => 1],
            'need_done_date' => ['cname' => '期望时间', 'is_output' => 1],
            'done_date' => ['cname' => '完成时间', 'is_output' => 1]
        ];
        $strAttachId = $this->objExcelWrite->init('需求明细')->createExcel(['需求明细' => $arrQaList], ['需求明细' => $arrColumnMap]);

        //返回
        return $strAttachId;
    }

    // -------------------------------------- qaQaInfo -------------------------------------- //

    /**
     * 送测
     */
    public function qaQaInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkQaQaInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->qaQa($arrParam);
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
    protected function checkQaQaInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['project_id', 'id'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
    }

    /**
     * 送测
     */
    protected function qaQa($arrParam) {
        $arrParams = [
            ':project_id' => $arrParam['project_id'],
            ':id' => $arrParam['id']
        ];
        $strSql = 'update qa set round=round+1,update_date=now() where id=:id and project_id=:project_id and round<5';
        $intRet = $this->objDB->setMainTable('qa')->update($strSql, $arrParams);
        return true;
    }

    // -------------------------------------- revokeQaInfo -------------------------------------- //

    /**
     * 撤销
     */
    public function revokeQaInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkRevokeQaInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->revokeQa($arrParam);
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
    protected function checkRevokeQaInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['project_id', 'id'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
    }

    /**
     * 撤销
     */
    protected function revokeQa($arrParam) {
        $this->objDB->setMainTable('qa')->beginTran();
        //qa
        $arrParams = [
            ':project_id' => $arrParam['project_id'],
            ':id' => $arrParam['id'],
            ':status' => '06'
        ];
        $strSql = 'update qa set status=:status,update_date=now() where id=:id and project_id=:project_id';
        $intRet = $this->objDB->setMainTable('qa')->update($strSql, $arrParams);
        if ($intRet <= 0) {
            $this->objDB->setMainTable('qa')->rollbackTran();
            return false;
        }
        //task
        $arrParams = [
            ':project_id' => $arrParam['project_id'],
            ':id' => $arrParam['id'],
            ':status' => '03'
        ];
        $strSql = "update task set status=:status,qa_batch_id='',change_file1='',change_file2='',change_file3='',change_file4='',change_file5='',update_date=now() where project_id=:project_id and qa_batch_id in (select batch_id from qa where id=:id)";
        $intRet = $this->objDB->setMainTable('qa')->update($strSql, $arrParams, true);
        if ($intRet <= 0) {
            $this->objDB->setMainTable('qa')->rollbackTran();
            return false;
        }
        $this->objDB->setMainTable('qa')->commitTran();
        return true;
    }

    // -------------------------------------- onlineQaInfo -------------------------------------- //

    /**
     * 上线
     */
    public function onlineQaInfo(&$strErrMsg) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkOnlineQaInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $blnRet = $this->onlineQa($arrParam);
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
    protected function checkOnlineQaInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['project_id', 'id'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
        $arrSummary = $arrParam['summary'];
        if (!is_array($arrSummary) || empty($arrSummary)) {
            return '请输入所有参与人员的bug总结';
        }
        foreach ($arrSummary as $summary) {
            if (empty($summary['key']) || empty($summary['value'])) {
                return '请输入所有参与人员的bug总结';
            }
        }
        $arrParam['summary'] = json_encode($arrParam['summary']);
    }

    /**
     * 上线
     */
    protected function onlineQa($arrParam) {
        $this->objDB->setMainTable('qa')->beginTran();
        //qa
        $arrParams = [
            ':project_id' => $arrParam['project_id'],
            ':id' => $arrParam['id'],
            ':status' => '02',
            ':summary' => $arrParam['summary'],
        ];
        $strSql = 'update qa set status=:status,summary=:summary,online_date=now(),update_date=now() where id=:id and project_id=:project_id';
        $intRet = $this->objDB->setMainTable('qa')->update($strSql, $arrParams);
        if ($intRet <= 0) {
            $this->objDB->setMainTable('qa')->rollbackTran();
            return false;
        }
        //task
        $arrParams = [
            ':project_id' => $arrParam['project_id'],
            ':id' => $arrParam['id'],
            ':status' => '05'
        ];
        $strSql = "update task set status=:status,online_date=now(),update_date=now() where project_id=:project_id and qa_batch_id in (select batch_id from qa where id=:id)";
        $intRet = $this->objDB->setMainTable('qa')->update($strSql, $arrParams, true);
        if ($intRet <= 0) {
            $this->objDB->setMainTable('qa')->rollbackTran();
            return false;
        }
        $this->objDB->setMainTable('qa')->commitTran();
        return true;
    }

    // -------------------------------------- downQaInfo -------------------------------------- //

    /**
     * 下载
     */
    public function downQaInfo(&$strErrMsg, &$arrData) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkDownQaInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $strAttachId = '';
        $blnRet = $this->downQa($arrParam, $strAttachId);
        //4.结果返回
        if (!$blnRet) {
            $strErrMsg = '下载失败';
            return false;
        }
        $arrData['attach_id'] = $strAttachId;
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkDownQaInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = Request::getAllParam();

        //2.字段自定义配置检查
        $arrRules = $this->arrRules;
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['project_id', 'id'], $arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }

        //3.字段数据库配置检查
        //4.业务检查
    }

    /**
     * 下载
     */
    protected function downQa($arrParam, &$strAttachId) {
        //1.获取数据
        $arrParams = [
            ':project_id' => $arrParam['project_id'],
            ':id' => $arrParam['id']
        ];
        $strSql1 = 'select a.id,a.task_name,b.cname devloper,a.need_memo,a.need_attach,
                        a.page_enter,a.dev_memo,a.change_file,a.need_tip,a.sql_attach,a.other_attach,
                        a.change_file1,a.change_file2,a.change_file3,a.change_file4,a.change_file5
                    from task a
                        join account b on a.account_id=b.id
                    where a.project_id=:project_id and a.qa_batch_id in (select batch_id from qa where id=:id)';
        $strSql2 = 'select qa_name,qa_tip,round from qa where id=:id and project_id=:project_id';
        $arrTask = $this->objDB->setMainTable('qa')->select($strSql1, $arrParams);
        $arrQa = $this->objDB->setMainTable('qa')->select($strSql2, $arrParams);

        //2.创建文件
        $strAttachId = getGUID();
        return $this->createAttach($strAttachId, $arrTask, $arrQa);
    }

    /**
     * 生成压缩文件
     */
    protected function createAttach($strAttachId, $arrTask, $arrQa) {
        $intRound = $arrQa[0]['round'];
        $strAttachPath = File::getDirPath(App::make('path.storage') . '/cache/file/' . 'download/' . $strAttachId);

        //1.需要生成的文件
        $strFileName1 = $strAttachPath . "/1.需求文档.html";
        $strFileName2 = $strAttachPath . "/2.修改文件.txt";
        $strFileName3 = $strAttachPath . "/3.SQL脚本附件地址.txt";
        $strFileName4 = $strAttachPath . "/4.需求附件地址.txt";
        $strFileName5 = $strAttachPath . "/5.开发附件地址.txt";
        for ($intTmpRound = 1; $intTmpRound <= $intRound; $intTmpRound++) {
            $intIndex = 5 + $intTmpRound;
            $strFileName = 'strFileName' . $intIndex;
            $$strFileName = $strAttachPath . "/{$intIndex}.第{$intTmpRound}轮送测bug修改文件.txt";
        }

        //2.解析数据
        $arr2 = []; //修改文件
        $arr2['html'] = [];
        $arr2['js'] = [];
        $arr2['css'] = [];
        $arr2['php'] = [];
        $arr2['other'] = [];
        $arr3 = []; //SQL脚本附件地址
        $arr4 = []; //需求附件地址
        $arr5 = []; //开发附件地址
        for ($intTmpRound = 1; $intTmpRound <= $intRound; $intTmpRound++) {
            //bug修改文件
            $strArrName = 'arr' . (5 + $intTmpRound);
            $$strArrName = [];
        }
        foreach ($arrTask as $arrTaskItem) {
            //2.修改文件
            $arrTmp = explode("\n", $arrTaskItem['change_file']);
            foreach ($arrTmp as $value) {
                $value = str_replace(' ', '', $value);
                if (strpos($value, '.html') !== false) {
                    if (!array_key_exists($value, $arr2['html'])) {
                        $arr2['html'][$value] = $value;
                    }
                } else if (strpos($value, '.css') !== false) {
                    if (!array_key_exists($value, $arr2['css'])) {
                        $arr2['css'][$value] = $value;
                    }
                } else if (strpos($value, '.js') !== false) {
                    if (!array_key_exists($value, $arr2['js'])) {
                        $arr2['js'][$value] = $value;
                    }
                } else if (strpos($value, '.php') !== false) {
                    if (!array_key_exists($value, $arr2['php'])) {
                        $arr2['php'][$value] = $value;
                    }
                } else {
                    if ($value !== '' && !array_key_exists($value, $arr2['other'])) {
                        $arr2['other'][$value] = $value;
                    }
                }
            }
            //3.SQL脚本附件地址
            if (!empty($arrTaskItem['sql_attach'])) {
                $arrTmp = explode("\n", $arrTaskItem['sql_attach']);
                foreach ($arrTmp as $value) {
                    $value = str_replace(' ', '', $value);
                    if (!array_key_exists($value, $arr3)) {
                        $arr3[$value] = $value;
                    }
                }
            }
            //4.需求附件地址
            if (!empty($arrTaskItem['need_attach'])) {
                $arrTmp = explode("\n", $arrTaskItem['need_attach']);
                foreach ($arrTmp as $value) {
                    $value = str_replace(' ', '', $value);
                    if (!array_key_exists($value, $arr4)) {
                        $arr4[$value] = $value;
                    }
                }
            }
            //5.开发附件地址   
            if (!empty($arrTaskItem['other_attach'])) {
                $arrTmp = explode("\n", $arrTaskItem['other_attach']);
                foreach ($arrTmp as $value) {
                    $value = str_replace(' ', '', $value);
                    if (!array_key_exists($value, $arr5)) {
                        $arr5[$value] = $value;
                    }
                }
            }
            //6.bug修改文件
            for ($intTmpRound = 1; $intTmpRound <= $intRound; $intTmpRound++) {
                if (!empty($arrTaskItem['change_file' . $intTmpRound])) {
                    $arrTmp = explode("\n", $arrTaskItem['change_file' . $intTmpRound]);
                    $strArrName = 'arr' . (5 + $intTmpRound);
                    foreach ($arrTmp as $value) {
                        $value = str_replace(' ', '', $value);
                        if (!array_key_exists($value, $$strArrName)) {
                            ${$strArrName}[$value] = $value;
                        }
                    }
                }
            }
        }
        //3.写入文件
        //3.1.需求文档        
        $strContent = "
        <!DOCTYPE html>
            <html>
            <head>
            <meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\">
            <style>	
                body{
                        font: 12px '微软雅黑', 'Microsoft Yahei', Helvetica Neue, Hiragino Sans GB, '宋体', 'simsun', '黑体', Arial, sans-serif;
                        line-height: 1.5;
                }
                .p {
                        margin: 5px 0px;
                        font-size:14px;
                        font-weight:bold;
                }
                .main{
                        width:90%;
                        margin:0 auto;
                }
                .title{
                        text-align:center;
                }
                .menu{
                        list-style: none;
                        margin: 0;
                        padding-left: 20px;
                        font-size: 14px;
                }
                .menu a{
                        text-decoration:none;
                        color:gray;
                }
                .menu a:hover{
                        color:black;
                }
                .content{
                        width:95%;
                        margin:0 auto;
                }
                .page_enter,.need_attach,.need_memo,.other_attach,.dev_memo,.sql_attach,.need_tip,.change_file{
                        padding: 5px 20px;
                }
                .need_tip{
                        color:red;
                }
            </style>
            </head>
                <body>
                    <div class=\"main\">";
        $strContent .= sprintf("<div class=\"title\"><h1>%s需求文档</h1></div>", $arrQa[0]['qa_name']);
        $strContent .= sprintf("<h3 style=\"color:red;\">%s</h3>", $arrQa[0]['qa_tip']);
        $strContent .= "<h2>需求列表</h2><ul class=\"menu\">";
        $intCount = 1;
        foreach ($arrTask as $arrTaskItem) {
            $strContent .= sprintf("<li>%s.<a href=\"#conetent%s\">%s</a></li>", $intCount, $arrTaskItem['id'], $arrTaskItem['task_name']);
            $intCount++;
        }
        $strContent .= "</ul>";
        $intCount = 1;
        foreach ($arrTask as $arrTaskItem) {
            $strContent .= sprintf("<h2 id=\"conetent%s\">需求%s(%s)：%s</h2>", $arrTaskItem['id'], $intCount, $arrTaskItem['devloper'], $arrTaskItem['task_name']);
            $strContent .= "<div class=\"content\">";

            $strContent .= "<div class=\"p\">页面入口：</div>";
            $strContent .= sprintf("<div class=\"page_enter\">%s</div>", $arrTaskItem['page_enter']);

            $strContent .= "<div class=\"p\">需求文档地址：</div>";
            $strContent .= sprintf("<div class=\"need_attach\">%s</div>", str_replace("\n", "<br/>", $arrTaskItem['need_attach']));

            $strContent .= "<div class=\"p\">需求说明：</div>";
            $strContent .= sprintf("<div class=\"need_memo\">%s</div>", htmlspecialchars_decode($arrTaskItem['need_memo']));

            $strContent .= "<div class=\"p\">开发文档地址：</div>";
            $strContent .= sprintf("<div class=\"other_attach\">%s</div>", str_replace("\n", "<br/>", $arrTaskItem['other_attach']));

            $strContent .= "<div class=\"p\">逻辑说明：</div>";
            $strContent .= sprintf("<div class=\"dev_memo\">%s</div>", htmlspecialchars_decode($arrTaskItem['dev_memo']));

            $strContent .= "<div class=\"p\">SQL脚本附件地址：</div>";
            $strContent .= sprintf("<div class=\"sql_attach\">%s</div>", str_replace("\n", "<br/>", $arrTaskItem['sql_attach']));

            $strContent .= "<div class=\"p\">注意事项：</div>";
            $strContent .= sprintf("<div class=\"need_tip\">%s</div>", str_replace("\n", "<br/>", $arrTaskItem['need_tip']));

            $strContent .= "<div class=\"p\">修改文件：</div>";
            $strContent .= sprintf("<div class=\"change_file\">%s</div>", str_replace("\n", "<br/>", $arrTaskItem['change_file']));

            $strContent .= "</div>";
            $intCount++;
        }
        $strContent .= "	
            </div>
            </body>
            </html>";
        file_put_contents("{$strFileName1}", "{$strContent}", FILE_APPEND | LOCK_EX);

        //3.2.修改文件
        $intCount = count($arr2['html']) + count($arr2['js']) + count($arr2['css']) + count($arr2['php']) + count($arr2['other']);
        $strContent = "文件数量：{$intCount}\r\n";
        $strContent .= "html文件\r\n";
        $strContent .= implode("\r\n", $arr2['html']) . "\r\n";
        $strContent .= "\r\njs文件\r\n";
        $strContent .= implode("\r\n", $arr2['js']) . "\r\n";
        $strContent .= "\r\ncss文件\r\n";
        $strContent .= implode("\r\n", $arr2['css']) . "\r\n";
        $strContent .= "\r\nphp文件\r\n";
        $strContent .= implode("\r\n", $arr2['php']) . "\r\n";
        $strContent .= "\r\n其他文件\r\n";
        $strContent .= implode("\r\n", $arr2['other']) . "\r\n";
        file_put_contents("{$strFileName2}", "{$strContent}", FILE_APPEND | LOCK_EX);

        //3.3.SQL脚本附件地址
        $strContent = "SQL脚本附件地址\r\n";
        $strContent .= implode("\r\n", $arr3);
        file_put_contents("{$strFileName3}", "{$strContent}", FILE_APPEND | LOCK_EX);

        //3.4.需求附件地址
        $strContent = "需求附件地址\r\n";
        $strContent .= implode("\r\n", $arr4);
        file_put_contents("{$strFileName4}", "{$strContent}", FILE_APPEND | LOCK_EX);

        //3.5.开发附件地址
        $strContent = "开发附件地址\r\n";
        $strContent .= implode("\r\n", $arr5);
        file_put_contents("{$strFileName5}", "{$strContent}", FILE_APPEND | LOCK_EX);

        //3.6.bug修改文件
        for ($intTmpRound = 1; $intTmpRound <= $intRound; $intTmpRound++) {
            $strContent = "bug修改文件\r\n";
            $strFileName = 'strFileName' . (5 + $intTmpRound);
            $strArrName = 'arr' . (5 + $intTmpRound);
            $intCount = count($$strArrName);
            $strContent .= "文件数量：{$intCount}\r\n";
            $strContent .= implode("\r\n", $$strArrName);
            file_put_contents("{$$strFileName}", "{$strContent}", FILE_APPEND | LOCK_EX);
        }

        //4.压缩文件
        $strZipFileName = App::make('path.storage') . '/cache/file/' . 'download/' . "{$strAttachId}.zip";
        $arrZip = [
            iconv('utf-8', 'gb2312', "1.需求文档.html") => $strFileName1,
            iconv('utf-8', 'gb2312', "2.修改文件.txt") => $strFileName2,
            iconv('utf-8', 'gb2312', "3.SQL脚本附件地址.txt") => $strFileName3,
            iconv('utf-8', 'gb2312', "4.需求附件地址.txt") => $strFileName4,
            iconv('utf-8', 'gb2312', "5.开发附件地址.txt") => $strFileName5
        ];
        for ($intTmpRound = 1; $intTmpRound <= $intRound; $intTmpRound++) {
            $strFileName = 'strFileName' . (5 + $intTmpRound);
            $intIndex = 5 + $intTmpRound;
            $arrZip[iconv('utf-8', 'gb2312', "{$intIndex}.第{$intTmpRound}轮送测bug修改文件.txt")] = $$strFileName;
        }
        $zipFlag = File::createZip($arrZip, $strZipFileName);
        rmdir($strAttachPath);

        //5.保存附件信息到数据库
        if ($zipFlag) {
            $arrParam['attach_id'] = $strAttachId;
            $arrParam['path'] = $strZipFileName;
            $arrParam['cname'] = $arrQa[0]['qa_name'] . '.zip';
            $arrParam['down_del'] = 1;
            return File::saveAttach($arrParam);
        } else {
            return false;
        }
    }

    // -------------------------------------- validator -------------------------------------- //
    // -------------------------------------- common -------------------------------------- //
}
