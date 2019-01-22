<?php

namespace Framework\Service\Database;

use Exception;
use Framework\Facade\Log;
use Framework\Facade\App;
use Framework\Facade\Cache;
use Framework\Facade\Config;
use Framework\Service\Database\DB;
use Framework\Service\Database\Core;

/**
 * 提供hash数据结构的便捷操作
 * 1.select:缓存没有，从库获取，如果库里有数据则同步缓存
 * 2.update
 * 3.insert:
 * 4.delete:
 */
class HashCacheDB {

    /**
     * 数据实例
     */
    protected $objDB;

    /**
     * core实例
     */
    protected $objCore;

    /**
     * 表信息
     */
    protected $arrTableInfo = [];

    /**
     * 表信息缓存目录
     */
    protected $strTableCacheDir = '';

    /**
     * 构造方法
     */
    public function __construct(DB $objDB) {
        $this->strTableCacheDir = App::make('path.storage') . '/cache/table/';
        $this->objDB = $objDB;
    }

    /**
     * 获取BLLCore实例对象
     */
    protected function getObjCore() {
        if (is_null($this->objCore)) {
            $this->objCore = new Core($this->objDB);
        }
        return $this->objCore;
    }

    /**
     * 初始化信息
     */
    protected function initInfo($strTableName) {
        if (empty($strTableName)) {
            throw new Exception('表不能为空');
        }

        if (array_key_exists($strTableName, $this->arrTableInfo)) {
            //已有信息无需处理
            return;
        }

        $this->arrTableInfo[$strTableName] = [];
        $this->arrTableInfo[$strTableName]['table'] = $this->getTableInfo($strTableName);
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

        //5.获取不到抛出异常
        throw new Exception(sprintf('[%s]表信息获取失败', $strTableName));
    }

    /**
     * 从缓存或数据库获取数据
     * @param string $strTableName 表名，如interview_accountjobseekwx_wx
     * @param string $strField 获取的字段，如account_id,wx_id,wx_appid
     * @param array $arrParam 查询条件(需要包含表配置的primary_key全部字段)，如[['account_id'=>1]]
     */
    public function select($strTableName, $strField, $arrParam) {
        try {
            //1.初始化表信息
            $this->initInfo($strTableName);
            //2.参数检查
            $arrKey = [];
            $this->checkSelect($strTableName, $strField, $arrParam, $arrKey);
            //3.缓存查询
            $arrResult = Cache::exec('hMGet', ['key' => $arrKey, 'field' => explode(',', $strField)]);
            $arrResult = $this->dealSelectResult($arrResult);
            //4.数据库查询
            if (empty($arrResult)) {
                $arrResult = $this->selectDB($strTableName, $strField, $arrParam);
                if (!empty($arrResult) && in_array($this->arrTableInfo[$strTableName]['table']['redis_type'], [2, 3])) {
                    //同步缓存
                    $this->sync($strTableName, $arrParam);
                }
            }
            //5.数据返回
            return $arrResult;
        } catch (Exception $e) {
            $strErrLog = sprintf("\n error:%s \n command:%s \n tablename:%s \n field:%s \n param:%s \n", $e->getMessage(), 'select', $strTableName, $strField, json_encode($arrParam));
            Log::log($strErrLog, Config::get('const.Log.LOG_REDISERR'));
            return [];
        }
    }

    /**
     * 向缓存插入数据
     * @param string $strTableName 表名，如interview_accountjobseekwx_wx
     * @param array $arrValue 插入数据，如[['id'=>1,'value'=>'a']]
     */
    public function insert($strTableName, $arrValue) {
        try {
            //1.初始化表信息
            $this->initInfo($strTableName);
            //1.参数检查
            $this->checkInsert($strTableName, $arrValue);
            //1.生成缓存数据
            $intExpire = $this->arrTableInfo[$strTableName]['table']['redis_expire'] * 3600;
            $arrPrimaryKey = explode(',', $this->arrTableInfo[$strTableName]['table']['primary_key']);
            $arrParam = [];
            foreach ($arrValue as $intIndex => $arrData) {
                $strkey = $strTableName;
                foreach ($arrPrimaryKey as $strKeyCol) {
                    $strkey = $strkey . '::' . strtolower($arrData[$strKeyCol]);
                }
                $arrParam[] = ['key' => $strkey, 'value' => $arrData, 'expire' => $intExpire];
            }
            //4.写入缓存
            return Cache::exec('hMSet', $arrParam);
        } catch (Exception $e) {
            $strErrLog = sprintf("\n error:%s \n command:%s \n tablename:%s \n field:%s \n param:%s \n", $e->getMessage(), 'insert', $strTableName, '', json_encode($arrValue));
            Log::log($strErrLog, Config::get('const.Log.LOG_REDISERR'));
            return false;
        }
    }

    /**
     * 同步数据到缓存
     */
    protected function sync($strTableName, $arrParam) {
        //1.获取数据库数据
        $strField = $this->arrTableInfo[$strTableName]['table']['primary_key'] . ',' . $this->arrTableInfo[$strTableName]['table']['redis_col'];
        $arrResult = $this->selectDB($strTableName, $strField, $arrParam);
        //2.生成缓存数据
        $arrPrimaryKey = explode(',', $this->arrTableInfo[$strTableName]['table']['primary_key']);
        $arrRedisSyncKey = [];
        foreach ($arrResult as $intIndex => $arrData) {
            $strkey = $strTableName;
            foreach ($arrPrimaryKey as $strKeyCol) {
                $strkey = $strkey . '::' . strtolower($arrData[$strKeyCol]);
            }
            //合并key处理
            if (!array_key_exists($strkey, $arrRedisSyncKey)) {
                $arrRedisSyncKey[$strkey] = $strkey;
            }
            $arrResult[$intIndex]['redis_sync_key'] = $strkey;
        }
        //如果同一key值有多条数据则进行合并
        $arrColName = array_keys($arrResult[0]);
        $arrResultTmp = [];
        foreach ($arrRedisSyncKey as $strKey) {
            $arrCol = [];
            foreach ($arrResult as $arrValue) {
                if ($arrValue['redis_sync_key'] == $strKey) {
                    foreach ($arrColName as $strColName) {
                        $arrCol[$strColName][] = $arrValue[$strColName];
                    }
                }
            }
            foreach ($arrCol as $strColName => $arrTmp) {
                $arrCol[$strColName] = implode('|', $arrTmp);
            }
            $arrCol['redis_sync_key'] = $strKey;
            $arrResultTmp[] = $arrCol;
        }
        $arrResult = $arrResultTmp;
        //3.写入缓存
        $arrParam = [];
        $intExpire = $this->arrTableInfo[$strTableName]['table']['redis_expire'] * 3600;
        foreach ($arrResult as $arrTmp) {
            $arrParam[] = ['key' => $arrTmp['redis_sync_key'], 'value' => $arrTmp, 'expire' => $intExpire];
        }
        return Cache::exec('hMSet', $arrParam);
    }

    /**
     * 从数据库查询数据
     */
    protected function selectDB($strTableName, $strField, $arrParam) {
        //1.where
        $strWhere = '';
        $arrParams = [];
        $arrWhereCol = array_keys($arrParam[0]);
        foreach ($arrParam as $intIndex => $arrTmp) {
            $strWhereTmp = '';
            foreach ($arrWhereCol as $strWhereCol) {
                $strWhereTmp.="and {$strWhereCol}=:{$strWhereCol}{$intIndex} ";
                $arrParams[":{$strWhereCol}{$intIndex}"] = $arrTmp[$strWhereCol];
            }
            $strWhereTmp = ltrim($strWhereTmp, 'and');
            $strWhere.="or ({$strWhereTmp})";
        }
        $strWhere = ltrim($strWhere, 'or');
        $strWhere = (empty($this->arrTableInfo[$strTableName]['table']['redis_condition'])) ? $strWhere : "({$strWhere}) and {$this->arrTableInfo[$strTableName]['table']['redis_condition']}";
        //2.拼接sql
        $strTableNameDB = $this->arrTableInfo[$strTableName]['table']['table_name_db'];
        $strSql = "select {$strField} from {$strTableNameDB} where {$strWhere}";
        //3.查询返回
        return $this->objDB->setMainTable($strTableNameDB)->select($strSql, $arrParams);
    }

    /**
     * 检查select参数
     */
    protected function checkSelect($strTableName, $strField, $arrParam, &$arrKey) {
        //1.param不能为空
        if (empty($arrParam)) {
            throw new Exception('查询条件不能为空');
        }
        //2.primarykey
        $arrParamCol = array_keys($arrParam[0]);
        $arrPrimaryCol = explode(',', $this->arrTableInfo[$strTableName]['table']['primary_key']);
        $arrDiff = array_diff($arrPrimaryCol, $arrParamCol);
        if (!empty($arrDiff)) {
            throw new Exception(sprintf('查询条件需要包含【%s】主键字段', implode(',', $arrDiff)));
        }
        //3.拼接key
        $arrKey = [];
        foreach ($arrParam as $arrTmp) {
            $strKey = $strTableName;
            foreach ($arrPrimaryCol as $strCol) {
                $strKey.='::' . strtolower($arrTmp[$strCol]);
            }
            $arrKey[] = $strKey;
        }
    }

    /**
     * 检查select参数
     */
    protected function checkInsert($strTableName, $arrParam) {
        //1.param不能为空
        if (empty($arrParam)) {
            throw new Exception('插入数据不能为空');
        }
        //2.primarykey
        $arrParamCol = array_keys($arrParam[0]);
        $arrPrimaryCol = explode(',', $this->arrTableInfo[$strTableName]['table']['primary_key']);
        $arrDiff = array_diff($arrPrimaryCol, $arrParamCol);
        if (!empty($arrDiff)) {
            throw new Exception(sprintf('插入数据需要包含【%s】主键字段', implode(',', $arrDiff)));
        }
    }

    /**
     * 去除结果中的无效值
     * 结果都为false的
     */
    protected function dealSelectResult($arrResult) {
        $arrResultTmp = [];
        foreach ($arrResult as $arrTmp) {
            $arrUnique = array_unique(array_values($arrTmp));
            if (count($arrUnique) == 1 && $arrUnique[0] === false) {
                continue;
            }
            $arrResultTmp[] = $arrTmp;
        }
        return $arrResultTmp;
    }

}
