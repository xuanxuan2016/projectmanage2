<?php

namespace Framework\Service\Database;

use Exception;
use Framework\Facade\App;
use Framework\Facade\Log;
use Framework\Facade\Config;
use Framework\Service\Database\Core;
use Framework\Service\Database\MySql;

class DB {

    /**
     * core实例
     */
    protected $objCore;

    /**
     * 当前执行操作的表
     */
    protected $strMainTable = '';

    /**
     * 表操作信息
     */
    protected $arrTableInfo = [];

    /**
     * 事务处理的pdo
     */
    protected $objTranPdo = null;

    /**
     * 表信息缓存目录
     */
    private $strTableCacheDir = '';

    /**
     * 业务信息缓存目录
     */
    private $strBusinessCacheDir = '';

    /**
     * 构造方法
     */
    public function __construct() {
        $this->strTableCacheDir = App::make('path.storage') . '/cache/table/';
        $this->strBusinessCacheDir = App::make('path.storage') . '/cache/business/';
    }

    /**
     * 获取BLLCore实例对象
     */
    protected function getObjCore() {
        if (is_null($this->objCore)) {
            $this->objCore = new Core(new self());
        }
        return $this->objCore;
    }

    /**
     * 设置操作主表
     */
    public function setMainTable($strMainTable) {
        $this->strMainTable = $strMainTable;
        return $this;
    }

    /**
     * 初始化信息
     */
    protected function initInfo() {
        if (empty($this->strMainTable)) {
            throw new Exception('操作主表不能为空');
        }

        if (array_key_exists($this->strMainTable, $this->arrTableInfo)) {
            //已有信息无需处理
            return;
        }

        $this->arrTableInfo[$this->strMainTable] = [];
        $this->arrTableInfo[$this->strMainTable]['table'] = $this->getTableInfo($this->strMainTable);
        $this->arrTableInfo[$this->strMainTable]['business'] = $this->getBusinessInfo();
        $this->arrTableInfo[$this->strMainTable]['pdo'] = $this->getPdoInfo();
    }

    /**
     * 获取表信息
     */
    protected function getTableInfo($strTableName) {
        //1.配置文件直接获取
        $arrTableInfo = Config::get("database.table_info.{$strTableName}");
        if (!empty($arrTableInfo)) {
            return $arrTableInfo;
        }

        //2.缓存文件获取
        $strFileName = $this->strTableCacheDir . $strTableName . '.log';
        if (file_exists($strFileName)) {
            $arrTableInfo = json_decode(file_get_contents($strFileName), true);
            if (is_array($arrTableInfo) && !empty($arrTableInfo)) {
                return $arrTableInfo;
            }
        }

        //3.数据库获取
        $arrTableInfo = $this->getObjCore()->getTableInfo($strTableName);
        if (!empty($arrTableInfo)) {
            //写入缓存文件
            file_put_contents($strFileName, json_encode($arrTableInfo));
            return $arrTableInfo;
        }

        //4.默认业务类型
        $arrTableInfo = ['business_type' => Config::get('database.default_business_type')];
        if (!empty($arrTableInfo['business_type'])) {
            return $arrTableInfo;
        }

        //5.获取不到抛出异常
        throw new Exception(sprintf('[%s]表信息获取失败', $strTableName));
    }

    /**
     * 获取业务信息
     */
    protected function getBusinessInfo() {
        //1.配置文件直接获取
        $strBusinessType = $this->arrTableInfo[$this->strMainTable]['table']['business_type'];
        $arrBusinessInfo = Config::get("database.business_info.{$strBusinessType}");
        if (!empty($arrBusinessInfo)) {
            return $arrBusinessInfo;
        }

        //2.缓存文件获取
        $strFileName = $this->strBusinessCacheDir . $strBusinessType . '.log';
        if (file_exists($strFileName)) {
            $arrBusinessInfo = json_decode(file_get_contents($strFileName), true);
            if (is_array($arrBusinessInfo) && !empty($arrBusinessInfo)) {
                return $arrBusinessInfo;
            }
        }

        //3.数据库获取
        $arrBusinessInfo = $this->getObjCore()->getBusinessInfo($strBusinessType);
        if (!empty($arrBusinessInfo)) {
            //写入缓存文件
            file_put_contents($strFileName, json_encode($arrBusinessInfo));
            return $arrBusinessInfo;
        }

        //4.获取不到抛出异常
        throw new Exception(sprintf('[%s]业务信息获取失败', $this->strMainTable));
    }

    /**
     * 获取分表信息
     */
    protected function getSubTable($arrParams, $arrTableInfo, &$strFBCol) {
        if (empty($arrTableInfo['fb_rule'])) {
            return $arrTableInfo['table_name_db'];
        }
        $arrFBRule = json_decode($arrTableInfo['fb_rule'], true);
        //1.分表字段是否在条件中
        $strFBCol = ':' . $arrFBRule['rule_col'];
        if (!array_key_exists($strFBCol, $arrParams)) {
            throw new Exception("{{$arrTableInfo['table_name_db']}}表，分表条件{{$strFBCol}}不在参数内" . json_encode($arrParams));
        }
        //2.分表规则是否支持
        if (!in_array($arrFBRule['rule_type'], ['%', 'date_formate'])) {
            throw new Exception("{{$arrTableInfo['table_name_db']}}表，分表规则{{$arrFBRule['rule_type']}}不支持");
        }
        //3.根据不同分表规则处理
        switch ($arrFBRule['rule_type']) {
            case '%':
                if (!checkFormat($arrParams[$strFBCol], Config::get('const.ValidFormat.FORMAT_INT'))) {
                    throw new Exception("{{$arrTableInfo['table_name_db']}}表，分表条件{{$arrFBRule['rule_col']}}的参数{{$arrParams[$strFBCol]}}格式不正确");
                }
                return $arrTableInfo['table_name_db'] . '_' . ($arrParams[$strFBCol] % $arrFBRule['rule_condition']);
                break;
            case 'date_formate':
                if (!checkFormat($arrParams[$strFBCol], Config::get('const.ValidFormat.FORMAT_DATETIME'))) {
                    throw new Exception("{{$arrTableInfo['table_name_db']}}表，分表条件{{$arrFBRule['rule_col']}}的参数{{$arrParams[$strFBCol]}}格式不正确");
                }
                return $arrTableInfo['table_name_db'] . '_' . date($arrFBRule['rule_condition'], strtotime($arrParams[$strFBCol]));
                break;
            case 'wx_id':
                if (empty($arrParams[$strFBCol])) {
                    throw new Exception("{{$arrTableInfo['table_name_db']}}表，分表条件{{$arrFBRule['rule_col']}}的参数{{$arrParams[$strFBCol]}}格式不正确");
                }
                $strWxId = $arrParams[$strFBCol];
                return $arrTableInfo['table_name_db'] . '_' . (ord($strWxId[strlen($strWxId) - 1]) % $arrFBRule['rule_condition']);
                break;
            default:
                return $arrTableInfo['table_name_db'];
                break;
        }
    }

    /**
     * 获取表操作对象
     */
    protected function getPdoInfo() {
        switch ($this->arrTableInfo[$this->strMainTable]['business']['master']['type']) {
            case 'mysql':
                return MySql::getInstance($this->arrTableInfo[$this->strMainTable]['business']);
                break;
        }

        throw new Exception(sprintf('[%s]操作对象获取失败', $this->strMainTable));
    }

    /**
     * 检查sql语句规范
     * @param string $strType sql语句类别   
     * @param string $strSql sql语句         
     * @param string $strErrMsg 错误信息
     * @return boolean
     */
    protected function checkSql($strType, $strSql, &$strErrMsg = '') {
        $strSql = strtolower($strSql);
        //1.去除换行符，前后空格
        $strSql = str_replace(["\r\n", "\r", "\n"], '', $strSql);
        $strSql = preg_replace('/(^\s*)|(\s*$)/', '', $strSql);
        //2.关键字信息
        preg_match_all('/^' . $strType . '\s+/i', $strSql, $arrSqlMatch, PREG_PATTERN_ORDER);
        if (empty($arrSqlMatch[0])) {
            $strErrMsg = sprintf("sql语句必须以%s开始", $strType);
            return false;
        }
        //2.insert,update,delete个数检查只能一个
        if (in_array($strType, ['insert', 'update', 'delete'])) {
            preg_match_all('/(^|\s+)' . $strType . '(\s+|$)/i', $strSql, $arrSqlMatch, PREG_PATTERN_ORDER);
            if (count($arrSqlMatch[0]) > 1) {
                $strErrMsg = sprintf("sql语句中%s需要唯一", $strType);
                return false;
            }
        }
        //3.update,delete,select必须与where个数匹配
        $arrSqlMatch = [[]];
        if (in_array($strType, ['update', 'delete'])) {
            preg_match_all('/(^|\s+)' . $strType . '(\s+|$)/i', $strSql, $arrSqlMatch, PREG_PATTERN_ORDER);
        }
        preg_match_all('/(^|\s+|\()select(\s+|$)/i', $strSql, $arrSqlSelect, PREG_PATTERN_ORDER);
        preg_match_all('/\s+where(\s+|$)/i', $strSql, $arrSqlWhere, PREG_PATTERN_ORDER);
        if (count($arrSqlMatch[0]) + count($arrSqlSelect[0]) != count($arrSqlWhere[0])) {
            $strErrMsg = sprintf("sql语句中,delete,update,select必须与where个数匹配");
            return false;
        }
        //4.update,delete,select表的关键条件检查
        if (isset($this->arrTableInfo[$this->strMainTable]['table']['opr_where'])) {
            $arrOprWhere = json_decode($this->arrTableInfo[$this->strMainTable]['table']['opr_where'], true);
            if (is_array($arrOprWhere) && !empty($arrOprWhere[$strType])) {
                $arrCol = explode(',', $arrOprWhere[$strType]);
                foreach ($arrCol as $strCol) {
                    if (!preg_match('/\s+where.*(\s+([a-z_]+\.)?' . $strCol . '\s*=)/', $strSql)) {
                        $strErrMsg = sprintf("sql语句中,where条件缺失必带字段{$strCol}");
                        return false;
                    }
                }
            }
        }
        //5.验证正确
        return true;
    }

    /**
     * 分表处理
     * @param string $strSql
     * @param string $strMainTable 主表名
     * @return string 处理之后的sql
     */
    private function dealFenBiao($strSql, &$arrParams) {
        $arrFlag = [];
        preg_match_all('/\[\s*[a-z_]+\s*\]/i', $strSql, $arrSqlMatch, PREG_PATTERN_ORDER);
        if (!empty($arrSqlMatch[0])) {
            $strFBCol = '';
            foreach ($arrSqlMatch[0] as $strTableName) {
                $strTmpTableName = preg_replace('/\s+|\[|\]/', '', $strTableName);
                $arrTableInfo = $this->getTableInfo($strTmpTableName);
                if (empty($arrFlag)) {
                    $arrFlag[] = $arrTableInfo['fb_rule'];
                }
                if (!empty($arrFlag) && !in_array($arrTableInfo['fb_rule'], $arrFlag)) {
                    throw new Exception('sql中不同表的分表规则不一致');
                }
                $strRealTableName = $this->getSubTable($arrParams, $arrTableInfo, $strFBCol);
                $strSql = str_replace($strTableName, $strRealTableName, $strSql);
            }
            //如果sql语句中不包含分表的字段，则从$arrParams中移除
            $strSql = str_replace(["\r\n", "\r", "\n"], '', $strSql);
            if ($strFBCol != '' && strpos($strSql, $strFBCol) === false) {
                unset($arrParams[$strFBCol]);
            }
        }
        return $strSql;
    }

    /**
     * 根据查询语句获取
     * @param string $strSql 查询语句
     */
    private function getSqlTotal($strSql) {
        //1.去除换行符，前后空格
        $strSql = str_replace(["\r\n", "\r", "\n"], '', $strSql);
        $strSql = preg_replace('/(^\s*)|(\s*$)/', '', $strSql);
        //2.替换查询字段为查询条数
        $arrSqlSplit = preg_split('/\s+from\s+/i', $strSql);
        $arrSqlSplit[0] = preg_replace('/\s+.*/i', ' count(*) total', $arrSqlSplit[0]);
        $strSql = implode(' from ', $arrSqlSplit);
        //3.去除末尾的排序条件
        $strSql = preg_replace('/order\s+by.*$/i', '', $strSql);
        return $strSql;
    }

    /**
     * 获取当前操作对象
     */
    protected function getPdo() {
        //如果有事务连接优先使用事务的连接，否则使用具体执行的连接
        return is_null($this->objTranPdo) ? $this->arrTableInfo[$this->strMainTable]['pdo'] : $this->objTranPdo;
    }

    /**
     * 开始事务，不支持跨库事务
     * 事务连接为beginTran时的数据库连接，忽略具体执行语句的连接信息
     */
    public function beginTran() {
        try {
            //1.初始化信息
            $this->initInfo();
            $this->objTranPdo = $this->arrTableInfo[$this->strMainTable]['pdo'];
            //2.开启事务
            $this->objTranPdo->beginTran();
            return true;
        } catch (Exception $e) {
            $strErrLog = 'beginTran:' . $e->getMessage();
            Log::log($strErrLog, Config::get('const.Log.LOG_SQLERR'));
            return false;
        }
    }

    /**
     * 回滚事务
     */
    public function rollbackTran() {
        try {
            $this->objTranPdo->rollbackTran();
            return true;
        } catch (Exception $e) {
            $strErrLog = 'rollbackTran:' . $e->getMessage();
            Log::log($strErrLog, Config::get('const.Log.LOG_SQLERR'));
            return false;
        } finally {
            $this->objTranPdo = null;
        }
    }

    /**
     * 提交事务
     */
    public function commitTran() {
        try {
            $this->objTranPdo->commitTran();
            return true;
        } catch (Exception $e) {
            $strErrLog = 'commitTran:' . $e->getMessage();
            Log::log($strErrLog, Config::get('const.Log.LOG_SQLERR'));
            return false;
        } finally {
            $this->objTranPdo = null;
        }
    }

    /**
     * 执行insert语句
     * <br>涉及表如果分表的话，表名使用[]包裹；涉及多个分表的话需要分表条件一致
     * @param string $strSql 需要执行的语句
     * @param array $arrParams 参数
     * @param bool $blnGetInsertID 是否要获取自增长主键，默认为true。
     * <br>true:如果有自增长主键的话返回主键ID,否则返回受影响行数
     * <br>false:始终返回受影响行数
     * @return int 主键或影响行数，如果出错返回-100
     */
    public function insert($strSql, array $arrParams = [], $blnGetInsertID = true) {
        try {
            //1.初始化信息
            $dateStartTime = $dateEndTime = $dateEndTime2 = getMicroTime();
            $this->initInfo();
            //2.sql检查
            if (!$this->checkSql('insert', $strSql, $strErrMsg)) {
                throw new Exception($strErrMsg);
            }
            //3.分表处理
            $strSql = $this->dealFenBiao($strSql, $arrParams);
            //4.具体处理
            $dateStartTime = getMicroTime();
            $intRes = $this->getPdo()->insert($strSql, $arrParams, $blnGetInsertID);
            $dateEndTime = getMicroTime();
            //5.返回结果
            return $intRes;
        } catch (Exception $e) {
            $dateEndTime2 = getMicroTime();
            $strErrLog = sprintf("\n error:%s \n sql:%s \n param:%s \n startdate:%s \n enddate:%s \n", $e->getMessage(), $strSql, json_encode($arrParams), $dateStartTime, $dateEndTime2);
            Log::log($strErrLog, Config::get('const.Log.LOG_SQLERR'));
            return -100;
        } finally {
            if (Config::get('database.log_info')) {
                $strErrLog = sprintf("\n sql:%s \n param:%s \n startdate:%s \n enddate:%s \n", $strSql, json_encode($arrParams), $dateStartTime, $dateEndTime);
                Log::log($strErrLog, Config::get('const.Log.LOG_SQLINFO'));
            }
        }
    }

    /**
     * 执行delete语句
     * <br>涉及表如果分表的话，表名使用[]包裹；涉及多个分表的话需要分表条件一致
     * @param string $strSql 需要执行的语句
     * @param array $arrParams 参数
     * @return int 返回执行语句的影响行数,如果出错返回-100
     */
    public function delete($strSql, array $arrParams = [], $blnGetInsertID = true) {
        try {
            //1.初始化信息
            $dateStartTime = $dateEndTime = $dateEndTime2 = getMicroTime();
            $this->initInfo();
            //2.sql检查
            if (!$this->checkSql('delete', $strSql, $strErrMsg)) {
                throw new Exception($strErrMsg);
            }
            //3.分表处理
            $strSql = $this->dealFenBiao($strSql, $arrParams);
            //4.具体处理
            $dateStartTime = getMicroTime();
            $intRes = $this->getPdo()->delete($strSql, $arrParams, $blnGetInsertID);
            $dateEndTime = getMicroTime();
            //5.返回结果
            return $intRes;
        } catch (Exception $e) {
            $dateEndTime2 = getMicroTime();
            $strErrLog = sprintf("\n error:%s \n sql:%s \n param:%s \n startdate:%s \n enddate:%s \n", $e->getMessage(), $strSql, json_encode($arrParams), $dateStartTime, $dateEndTime2);
            Log::log($strErrLog, Config::get('const.Log.LOG_SQLERR'));
            return -100;
        } finally {
            if (Config::get('database.log_info')) {
                $strErrLog = sprintf("\n sql:%s \n param:%s \n startdate:%s \n enddate:%s \n", $strSql, json_encode($arrParams), $dateStartTime, $dateEndTime);
                Log::log($strErrLog, Config::get('const.Log.LOG_SQLINFO'));
            }
        }
    }

    /**
     * 执行update语句
     * <br>涉及表如果分表的话，表名使用[]包裹；涉及多个分表的话需要分表条件一致
     * @param string $strSql 需要执行的语句
     * @param array $arrParams 参数
     * @param bool $blnMoreThanOne 影响行数是否可超过1，默认不能
     * @return int 返回执行语句的影响行数,如果出错返回-100
     */
    public function update($strSql, array $arrParams = [], $blnMoreThanOne = false) {
        try {
            //1.初始化信息
            $dateStartTime = $dateEndTime = $dateEndTime2 = getMicroTime();
            $this->initInfo();
            //2.sql检查
            if (!$this->checkSql('update', $strSql, $strErrMsg)) {
                throw new Exception($strErrMsg);
            }
            //3.分表处理
            $strSql = $this->dealFenBiao($strSql, $arrParams);
            //4.具体处理
            $dateStartTime = getMicroTime();
            $intRes = $this->getPdo()->update($strSql, $arrParams);
            $dateEndTime = getMicroTime();
            //5.影响行数判断
            if (!$blnMoreThanOne && $intRes > 1) {
                throw new Exception('影响行数超过预期行数1');
            }
            //6.返回结果
            return $intRes;
        } catch (Exception $e) {
            $dateEndTime2 = getMicroTime();
            $strErrLog = sprintf("\n error:%s \n sql:%s \n param:%s \n startdate:%s \n enddate:%s \n", $e->getMessage(), $strSql, json_encode($arrParams), $dateStartTime, $dateEndTime2);
            Log::log($strErrLog, Config::get('const.Log.LOG_SQLERR'));
            return -100;
        } finally {
            if (Config::get('database.log_info')) {
                $strErrLog = sprintf("\n sql:%s \n param:%s \n startdate:%s \n enddate:%s \n", $strSql, json_encode($arrParams), $dateStartTime, $dateEndTime);
                Log::log($strErrLog, Config::get('const.Log.LOG_SQLINFO'));
            }
        }
    }

    /**
     * 执行select语句，无需分页。
     * <br>涉及表如果分表的话，表名使用[]包裹；涉及多个分表的话需要分表条件一致。
     * @param string $strSql 需要执行的语句
     * @param array $arrParams 参数
     * @param bool $blnException 是否出现异常，默认为false。如果出现异常值为true
     * @return array 返回语句的查询结果,如果出错返回空数组
     */
    public function select($strSql, array $arrParams = [], &$blnException = false) {
        try {
            //1.初始化信息
            $dateStartTime = $dateEndTime = $dateEndTime2 = getMicroTime();
            $this->initInfo();
            //2.sql检查
            if (!$this->checkSql('select', $strSql, $strErrMsg)) {
                throw new Exception($strErrMsg);
            }
            //3.分表处理
            $strSql = $this->dealFenBiao($strSql, $arrParams);
            //4.具体处理
            $dateStartTime = getMicroTime();
            $arrRes = $this->getPdo()->select($strSql, $arrParams);
            $dateEndTime = getMicroTime();
            //5.返回结果
            return $arrRes;
        } catch (Exception $e) {
            $dateEndTime2 = getMicroTime();
            $strErrLog = sprintf("\n error:%s \n sql:%s \n param:%s \n startdate:%s \n enddate:%s \n", $e->getMessage(), $strSql, json_encode($arrParams), $dateStartTime, $dateEndTime2);
            Log::log($strErrLog, Config::get('const.Log.LOG_SQLERR'));
            $blnException = true;
            return [];
        } finally {
            if (Config::get('database.log_info')) {
                $strErrLog = sprintf("\n sql:%s \n param:%s \n startdate:%s \n enddate:%s \n", $strSql, json_encode($arrParams), $dateStartTime, $dateEndTime);
                Log::log($strErrLog, Config::get('const.Log.LOG_SQLINFO'));
            }
        }
    }

    /**
     * 执行select语句，需分页
     * @param string $strSql 数据集语句
     * @param int $intStart 开始行
     * @param int $intPageSize 每页条数
     * @param int $intTotal 总记录数
     * @param array $arrParams 参数
     * @param bool $blnNeedTotal 是否需要总记录，默认为否。如果需要总记录值为true
     * @param bool $blnException 是否出现异常，默认为false。如果出现异常值为true
     * @return array 返回语句的查询结果,如果出错返回空数组
     */
    public function selectPage($strSql, $intStart = 0, $intPageSize = PHP_INT_MAX, &$intTotal = 0, array $arrParams = [], $blnNeedTotal = false, &$blnException = false) {
        try {
            //1.初始化信息
            $dateStartTime = $dateEndTime = $dateEndTime2 = getMicroTime();
            $this->initInfo();
            //2.sql检查
            if (!$this->checkSql('select', $strSql, $strErrMsg)) {
                throw new Exception($strErrMsg);
            }
            //3.分表处理
            $strSql = $this->dealFenBiao($strSql, $arrParams);
            //4.具体处理
            $strSqlTotal = $this->getSqlTotal($strSql);
            $dateStartTime = getMicroTime();
            //详细数据
            $arrRes = $this->getPDO()->selectPage($strSql, $intStart, $intPageSize, $arrParams);
            if ($blnNeedTotal) {
                //总记录
                $arrTotal = $this->getPDO()->select($strSqlTotal, $arrParams);
                $strSql = $strSql . "\r\nTotalSql：" . $strSqlTotal;
                if (empty($arrTotal)) {
                    throw new Exception('获取总记录错误');
                }
                $intTotal = intval($arrTotal[0]['total']);
            }
            $dateEndTime = getMicroTime();
            //5.返回结果
            return $arrRes;
        } catch (Exception $e) {
            $dateEndTime2 = getMicroTime();
            $strErrLog = sprintf("\n error:%s \n sql:%s \n param:%s \n startdate:%s \n enddate:%s \n", $e->getMessage(), $strSql, json_encode($arrParams), $dateStartTime, $dateEndTime2);
            Log::log($strErrLog, Config::get('const.Log.LOG_SQLERR'));
            $blnException = true;
            return [];
        } finally {
            if (Config::get('database.log_info')) {
                $strErrLog = sprintf("\n sql:%s \n param:%s \n startdate:%s \n enddate:%s \n", $strSql, json_encode($arrParams), $dateStartTime, $dateEndTime);
                Log::log($strErrLog, Config::get('const.Log.LOG_SQLINFO'));
            }
        }
    }

    /**
     * 执行存储过程
     * @param string $strProcName 需要执行的存储过程，如sp_test
     * @param string $strProcParams sp参数，如:id
     * @param array $arrParams 参数数组，如[":id"=>"999"]
     * @param array $arrReturn 返回存储过程的查询结果,如果出错返回空数组
     * @return bool 是否成功
     */
    public function proc($strProcName, $strProcParams, $arrParams = [], &$arrReturn = []) {
        try {
            //1.初始化信息
            $dateStartTime = $dateEndTime = $dateEndTime2 = getMicroTime();
            //2.具体处理
            $dateStartTime = getMicroTime();
            $blnRes = $this->getPDO()->proc($strProcName, $strProcParams, $arrParams, $arrReturn);
            $dateEndTime = getMicroTime();
            //3.返回结果
            return $blnRes;
        } catch (Exception $e) {
            $dateEndTime2 = getMicroTime();
            $strErrLog = sprintf("\n error:%s \n sql:exec %s %s \n param:%s \n startdate:%s \n enddate:%s \n", $e->getMessage(), $strProcName, $strProcParams, json_encode($arrParams), $dateStartTime, $dateEndTime2);
            Log::log($strErrLog, Config::get('const.Log.LOG_SQLERR'));
            $blnException = true;
            return [];
        } finally {
            if (Config::get('database.log_info')) {
                $strErrLog = sprintf("\n sql:exec %s %s \n param:%s \n startdate:%s \n enddate:%s \n", $strProcName, $strProcParams, json_encode($arrParams), $dateStartTime, $dateEndTime);
                Log::log($strErrLog, Config::get('const.Log.LOG_SQLINFO'));
            }
        }
    }

}
