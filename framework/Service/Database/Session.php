<?php

namespace Framework\Service\Database;

use Framework\Facade\Log;
use Framework\Facade\Config;
use Framework\Service\Database\DB;

/**
 * Session handler类
 * 当redis失效时，将session保存到数据库
 */
class Session {

    /**
     * 数据实例
     */
    protected $objDB;

    /**
     * 构造函数
     */
    public function __construct(DB $objDB) {
        $this->objDB = $objDB;
    }

    /**
     * 初始化handler，设置自定义session的存储方法
     */
    public function initHandler() {
        session_set_save_handler(
                [$this, 'open'], [$this, 'close'], [$this, 'read'], [$this, 'write'], [$this, 'destroy'], [$this, 'gc']
        );
    }

    /**
     * 当调用session_start的时候调用，这里直接返回true
     * @param string $strSavePath session路径
     * @param string $strSessionName session名称
     */
    public function open($strSavePath, $strSessionName) {
        return true;
    }

    /**
     * write调用后被调用，这里直接返回true
     */
    public function close() {
        return true;
    }

    /**
     * 获取session值
     */
    public function read($strSessionId) {
        $strSql = "select session_data from interview_session where session_id=:session_id";
        $arrParams[":session_id"] = $strSessionId;
        $arrRes = $this->objDB->setMainTable('interview_session')->select($strSql, $arrParams);
        $strSessionData = "";
        if (empty($arrRes)) {
            $strSessionData = "";
        } else {
            $strSessionData = $arrRes[0]["session_data"];
        }
        return $strSessionData;
    }

    /**
     * 设置session值
     */
    public function write($strSessionId, $strSessionData) {
        //查询session_id对应session是否已经存在
        $strSql = "select session_id from interview_session where session_id=:session_id";
        $arrParams[":session_id"] = $strSessionId;
        $arrRes = $this->objDB->setMainTable('interview_session')->select($strSql, $arrParams);
        if (empty($arrRes)) {
            //如果session不存在，则插入一条新的session数据
            $strSql = "insert into interview_session(session_id, session_data) values(:session_id, :session_data)";
            $arrParams[":session_id"] = $strSessionId;
            $arrParams[":session_data"] = $strSessionData;
            $intRes = $this->objDB->setMainTable('interview_session')->insert($strSql, $arrParams);
            if ($intRes < 1) {
                //记录新增失败日志
                Log::log("Session Write (新增失败)(SessionId=$strSessionId, SessionData=$strSessionData)", Config::get('const.Log.LOG_SQLERR'));
                return false;
            }
        } else {
            //session存在，直接更新
            $strSql = "update interview_session set session_data=:session_data, update_date=now(3) where session_id=:session_id";
            $arrParams[":session_data"] = $strSessionData;
            $arrParams[":session_id"] = $strSessionId;
            $intRes = $this->objDB->setMainTable('interview_session')->update($strSql, $arrParams);
            if ($intRes < 1) {
                //记录更新失败日志
                Log::log("Session Write (更新失败)(SessionId=$strSessionId, SessionData=$strSessionData)", Config::get('const.Log.LOG_SQLERR'));
                return false;
            }
        }
        return true;
    }

    /**
     * 销毁session值
     */
    public function destroy($strSessionId) {
        $strSql = "delete from interview_session where session_id=:session_id";
        $arrParams[":session_id"] = $strSessionId;
        $intRes = $this->objDB->setMainTable('interview_session')->delete($strSql, $arrParams);
        if ($intRes < 1) {
            //记录失败日志
            Log::log("Session Destroy (失败) (SessionId=$strSessionId)", Config::get('const.Log.LOG_SQLERR'));
            return false;
        }
        return true;
    }

    /**
     * php回收过期session
     */
    public function gc($intLifeTime) {
        // 删除所有过期的session
        $strSql = "delete from interview_session where timestampdiff(second, update_date, now()) >= :lifetime";
        $arrParams[":lifetime"] = $intLifeTime;
        $intRes = $this->objDB->setMainTable('interview_session')->delete($strSql, $arrParams);
        if ($intRes < 0) {
            //记录失败日志
            Log::log("Session GC (失败) ($intRes sessions been deleted)", Config::get('const.Log.LOG_SQLERR'));
            return false;
        }
        return true;
    }

}
