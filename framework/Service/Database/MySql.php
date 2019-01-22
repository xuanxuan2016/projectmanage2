<?php

namespace Framework\Service\Database;

use PDO;
use Exception;
use PDOException;
use PDOStatement;
use Framework\Facade\Log;
use Framework\Facade\Des;
use Framework\Facade\Config;

/**
 * mysql库
 */
class MySql {

    /**
     * 数据库操作实例集合，同一个业务只创建一次数据库连接
     */
    private static $arrInstance = [];

    /**
     * 数据库读连接
     */
    private $objPdoRead;

    /**
     * 数据库写连接
     */
    private $objPdoWrite;

    /**
     * 语句对象
     */
    private $objPdoStmt;

    /**
     * 数据库连接信息
     */
    private $arrConnectInfo = [];

    /**
     * 是否正在事务中
     */
    private $intTran = 0;

    /**
     * 数据库连接信息(失败)
     */
    private $arrConnectInfoErr = [];

    /**
     * 重试标记
     */
    private $blnTryFlag = false;

    /**
     * 获取数据库操作对象
     * @param array $arrConnectInfo 连接信息
     */
    public static function getInstance($arrConnectInfo) {
        $strBusinessType = $arrConnectInfo['master']['business'];
        if (!isset(self::$arrInstance[$strBusinessType]) || !(self::$arrInstance[$strBusinessType] instanceof self)) {
            self::$arrInstance[$strBusinessType] = new self($arrConnectInfo);
        }
        return self::$arrInstance[$strBusinessType];
    }

    /**
     * 构造方法
     */
    private function __construct($arrConnectInfo) {
        $this->arrConnectInfo = $arrConnectInfo;
    }

    /**
     * 获取记录错误连接的key值
     */
    private function getErrKey($arrConnectInfo) {
        return $arrConnectInfo['host'] . '.' . $arrConnectInfo['port'];
    }

    /**
     * 主库是否参与读处理
     */
    private function getMasterRead() {
        return Config::get('database.' . $this->arrConnectInfo['master']['business'] . '.master_read');
    }

    /**
     * 连接超时时间
     */
    private function getConnectTimeout() {
        return Config::get('database.' . $this->arrConnectInfo['master']['business'] . '.connect_timeout');
    }

    /**
     * 是否使用长连接
     */
    private function getPersistent() {
        return Config::get('database.' . $this->arrConnectInfo['master']['business'] . '.persistent');
    }

    /**
     * 获取读连接的数据库连接信息
     */
    private function getPdoReadConnectInfo() {
        $arrConnectInfoTmp = [];
        if ($this->getMasterRead() == 1) {
            if (!in_array($this->getErrKey($this->arrConnectInfo['master']), $this->arrConnectInfoErr)) {
                $arrConnectInfoTmp[] = $this->arrConnectInfo['master'];
            }
        }
        foreach ($this->arrConnectInfo['slave'] as $arrValue) {
            if (!in_array($this->getErrKey($arrValue), $this->arrConnectInfoErr)) {
                $arrConnectInfoTmp[] = $arrValue;
            }
        }
        if (empty($arrConnectInfoTmp)) {
            return [];
        }
        $intIndex = mt_rand(0, count($arrConnectInfoTmp) - 1);
        return $arrConnectInfoTmp[$intIndex];
    }

    /**
     * 获取写连接的数据库连接信息
     */
    private function getPdoWriteConnectInfo() {
        return $this->arrConnectInfo['master'];
    }

    /**
     * 获取读连接
     * 使用master或slave配置
     */
    private function getPdoRead() {
        //错误信息
        $arrErrMsg = [];
        $this->arrConnectInfoErr = [];
        //尝试找到可用连接
        while (!$this->objPdoRead instanceof PDO && !empty($this->getPdoReadConnectInfo())) {
            try {
                $arrConnectInfo = $this->getPdoReadConnectInfo();
                $strDsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=UTF8;', $arrConnectInfo['host'], $arrConnectInfo['port'], $arrConnectInfo['db']);
                $this->objPdoRead = new PDO($strDsn, $arrConnectInfo['username'], Des::decrypt($arrConnectInfo['password']), [PDO::ATTR_TIMEOUT => $this->getConnectTimeout(), PDO::ATTR_PERSISTENT => $this->getPersistent()]);
                $this->objPdoRead->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //有错误时抛出异常
            } catch (PDOException $e) {
                $this->arrConnectInfoErr[] = $this->getErrKey($arrConnectInfo);
                $arrErrMsg[] = json_encode($arrConnectInfo) . "\n" . $e->getMessage();
            }
        }
        //有错误日志记录
        if (!empty($arrErrMsg)) {
            Log::log('连接数据库异常：' . json_encode($arrErrMsg), Config::get('const.Log.LOG_SQLERR'));
        }
        //最终是否连接到数据库
        if (!$this->objPdoRead instanceof PDO) {
            throw new Exception('连接数据库出错：' . json_encode($arrErrMsg) . "\n");
        }
        return $this->objPdoRead;
    }

    /**
     * 获取读连接
     * 使用master配置
     */
    private function getPdoWrite() {
        try {
            if (!$this->objPdoWrite instanceof PDO) {
                $arrConnectInfo = $this->getPdoWriteConnectInfo();
                $strDsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=UTF8;', $arrConnectInfo['host'], $arrConnectInfo['port'], $arrConnectInfo['db']);
                $this->objPdoWrite = new PDO($strDsn, $arrConnectInfo['username'], Des::decrypt($arrConnectInfo['password']), [PDO::ATTR_TIMEOUT => 3]);
                $this->objPdoWrite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //有错误时抛出异常
            }
            return $this->objPdoWrite;
        } catch (PDOException $e) {
            throw new Exception('连接数据库出错：' . json_encode($arrConnectInfo) . "\n" . $e->getMessage() . "\n");
        }
    }

    /**
     * 获取连接
     */
    private function getPdo($strType) {
        //如果在事务中始终返回读连接
        if ($this->intTran == 1) {
            return $this->getPdoWrite();
        }
        //不在事务中，根据使用情况返回
        switch ($strType) {
            case 'insert':
            case 'update':
            case 'delete':
            case 'proc':
                return $this->getPdoWrite();
                break;
            case 'select':
                return $this->getPdoRead();
                break;
        }
    }

    /**
     * 私有化克隆方法
     */
    private function __clone() {
        
    }

    /**
     * 私有化重建方法
     */
    private function __wakeup() {
        
    }

    /**
     * 开始事务
     */
    public function beginTran() {
        try {
            if ($this->intTran == 0) {
                if (!$this->getPdoWrite()->beginTransaction()) {
                    throw new Exception("BeginTran出错\n");
                }
                $this->intTran = 1;
                $this->blnTryFlag = false;
            }
        } catch (Exception $e) {
            if (strtolower($e->getCode()) == 'hy000' && $this->intTran == 0 && $this->blnTryFlag === false) {
                //连接异常，重置连接后执行
                $this->blnTryFlag = true;
                $this->objPdoWrite = null;
                return $this->beginTran();
            }
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 回滚事务
     */
    public function rollbackTran() {
        try {
            if ($this->intTran == 1) {
                if (!$this->getPdoWrite()->rollBack()) {
                    throw new Exception("RollbackTran出错\n");
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } finally {
            $this->intTran = 0;
        }
    }

    /**
     * 提交事务
     */
    public function commitTran() {
        try {
            if ($this->intTran == 1) {
                if (!$this->getPdoWrite()->commit()) {
                    throw new Exception("CommitTran出错\n");
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } finally {
            $this->intTran = 0;
        }
    }

    /**
     * 执行insert语句
     * @param string $strSql 需要执行的语句
     * @param array $arrParams 参数
     * @param bool $blnGetInsertID 是否要获取自增长主键，默认为true
     * @return int 如果有自增长主键的话返回主键ID,否则返回受影响行数
     */
    public function insert($strSql, array $arrParams = array(), $blnGetInsertID = true) {
        try {
            //1.参数处理
            $arrParams = $this->dealParam($strSql, $arrParams);
            //2.具体处理
            $this->objPdoStmt = $this->getPdo('insert')->prepare($strSql);
            $this->executeStatement($this->objPdoStmt, $arrParams);
            if ($blnGetInsertID && ctype_digit($this->getPdo('insert')->lastInsertId()) && $this->getPdo('insert')->lastInsertId() != '0') {
                $intRes = $this->getPdo('insert')->lastInsertId();
            } else {
                $intRes = $this->objPdoStmt->rowCount();
            }
            //3.重置标记
            $this->blnTryFlag = false;
            //4.返回结果
            return $intRes;
        } catch (Exception $e) {
            if (strtolower($e->getCode()) == 'hy000' && $this->intTran == 0 && $this->blnTryFlag === false) {
                //连接异常，重置连接后执行
                $this->blnTryFlag = true;
                $this->objPdoWrite = null;
                return $this->insert($strSql, $arrParams, $blnGetInsertID);
            }
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 执行delete语句
     * @param string $strSql 需要执行的语句
     * @param array $arrParams 参数
     * @return int 返回执行语句的影响行数,如果出错返回-100
     */
    public function delete($strSql, array $arrParams = array()) {
        try {
            //1.参数处理
            $arrParams = $this->dealParam($strSql, $arrParams);
            //2.具体处理
            $this->objPdoStmt = $this->getPdo('delete')->prepare($strSql);
            $this->executeStatement($this->objPdoStmt, $arrParams);
            $intRes = $this->objPdoStmt->rowCount();
            //3.重置标记
            $this->blnTryFlag = false;
            //4.返回结果
            return $intRes;
        } catch (Exception $e) {
            if (strtolower($e->getCode()) == 'hy000' && $this->intTran == 0 && $this->blnTryFlag === false) {
                //连接异常，重置连接后执行
                $this->blnTryFlag = true;
                $this->objPdoWrite = null;
                return $this->delete($strSql, $arrParams);
            }
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 执行Update语句
     * @param string $strSql 需要执行的语句
     * @param array $arrParams 参数
     * @return int 返回执行语句的影响行数,如果出错返回-100
     */
    public function update($strSql, array $arrParams = array()) {
        try {
            //1.参数处理
            $arrParams = $this->dealParam($strSql, $arrParams);
            //2.具体处理
            $this->objPdoStmt = $this->getPdo('update')->prepare($strSql);
            $this->executeStatement($this->objPdoStmt, $arrParams);
            $intRes = $this->objPdoStmt->rowCount();
            //3.重置标记
            $this->blnTryFlag = false;
            //4.返回结果
            return $intRes;
        } catch (Exception $e) {
            if (strtolower($e->getCode()) == 'hy000' && $this->intTran == 0 && $this->blnTryFlag === false) {
                //连接异常，重置连接后执行
                $this->blnTryFlag = true;
                $this->objPdoWrite = null;
                return $this->update($strSql, $arrParams);
            }
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 执行select语句，无需分页
     * @param string $strSql 需要执行的语句
     * @param array $arrParams 参数
     * @return array 返回语句的查询结果
     */
    public function select($strSql, array $arrParams = array()) {
        try {
            //1.参数处理
            $arrParams = $this->dealParam($strSql, $arrParams);
            //2.具体处理
            $this->objPdoStmt = $this->getPdo('select')->prepare($strSql);
            $this->executeStatement($this->objPdoStmt, $arrParams);
            $arrRes = $this->objPdoStmt->fetchAll(PDO::FETCH_ASSOC);
            //3.重置标记
            $this->blnTryFlag = false;
            //4.返回结果
            return $arrRes;
        } catch (Exception $e) {
            if (strtolower($e->getCode()) == 'hy000' && $this->intTran == 0 && $this->blnTryFlag === false) {
                //连接异常，重置连接后执行
                $this->blnTryFlag = true;
                $this->objPdoRead = null;
                return $this->select($strSql, $arrParams);
            }
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 执行select语句，需分页
     * @param string $strSql 数据集语句
     * @param int $intStart 开始行
     * @param int $intPageSize 每页条数
     * @param array $arrParams 参数
     * @return array 返回语句的查询结果,如果出错返回空数组
     */
    public function selectPage(&$strSql, $intStart = 0, $intPageSize = PHP_INT_MAX, array $arrParams = array()) {
        try {
            //1.参数处理
            $strSqlOld = $strSql;
            $arrParams = $this->dealParam($strSql, $arrParams);
            //2.分页查询语句
            $strSql = $this->getSqlPage($strSql, $intStart, $intPageSize);
            //3.具体处理
            $this->objPdoStmt = $this->getPdo('select')->prepare($strSql);
            $this->executeStatement($this->objPdoStmt, $arrParams);
            $arrRes = $this->objPdoStmt->fetchAll(PDO::FETCH_ASSOC);
            //4.重置标记
            $this->blnTryFlag = false;
            //5.返回结果
            return $arrRes;
        } catch (Exception $e) {
            if (strtolower($e->getCode()) == 'hy000' && $this->intTran == 0 && $this->blnTryFlag === false) {
                //连接异常，重置连接后执行
                $this->blnTryFlag = true;
                $this->objPdoRead = null;
                return $this->selectPage($strSqlOld, $intStart, $intPageSize, $arrParams);
            }
            throw new Exception($e->getMessage());
        } finally {
            $strSql = sprintf("\nOldSql：%s \nNewSql：%s", $strSqlOld, $strSql);
        }
    }

    /**
     * 执行存储过程
     * @param string $strProcName 需要执行的存储过程，如sp_test
     * @param string $strProcParams sp参数，如:id
     * @param array $arrParams 参数数组，如array(":id"=>"999")
     * @param array $arrReturn 返回存储过程的查询结果,如果出错返回空数组
     * @return bool 是否成功
     */
    public function proc($strProcName, $strProcParams, $arrParams = array(), &$arrReturn = array()) {
        try {
            //1.具体处理
            $this->objPdoStmt = $this->getPdo('proc')->prepare(sprintf('call %s(%s)', $strProcName, $strProcParams));
            $this->executeStatement($this->objPdoStmt, $arrParams);
            //2.循环读取所有数据集
            do {
                if ($this->objPdoStmt->columnCount() > 0) {
                    $arrReturn[] = $this->objPdoStmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } while ($this->objPdoStmt->nextRowset());
            //3.重置标记
            $this->blnTryFlag = false;
            //4.返回正确标识
            return true;
        } catch (Exception $e) {
            if (strtolower($e->getCode()) == 'hy000' && $this->intTran == 0 && $this->blnTryFlag === false) {
                //连接异常，重置连接后执行
                $this->blnTryFlag = true;
                $this->objPdoWrite = null;
                return $this->proc($strProcName, $strProcParams, $arrParams, $arrReturn);
            }
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 执行prepare的语句
     * @param PDOStatement $objPdoStmt prepare的statement
     * @param array $arrParams 参数
     */
    private function executeStatement(PDOStatement $objPdoStmt, array $arrParams) {
        if (!$objPdoStmt->execute($arrParams)) {
            throw new Exception('执行statement错误');
        }
    }

    /**
     * 处理参数
     * @param array $arrParams
     */
    private function dealParam($strSql, array $arrParams = array()) {
        return $arrParams;
    }

    /**
     * 获取分页查询语句
     * @param string $strSql 查询语句
     */
    private function getSqlPage($strSql, $intStart, $intPageSize) {
        $strSql = sprintf('%s limit %s,%s', $strSql, is_numeric($intStart) ? $intStart : 0, is_numeric($intPageSize) ? $intPageSize : 0);
        return $strSql;
    }

}
