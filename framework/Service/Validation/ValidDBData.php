<?php

namespace Framework\Service\Validation;

use Framework\Facade\App;
use Framework\Facade\Config;
use Framework\Service\Database\DB;

/**
 * 字段数据库配置检查
 * data_type
 * 数字
 * 2=>('bigint','numeric','bit','smallint','decimal','smallmoney','int','tinyint','money','float','real')
 * 日期
 * 3=>('date','datetime','timestamp','time','year')
 * 字符
 * 1=>('mediumblob','mediumtext','longblob','longtext','blob','text','tinyblob','tinytext','char','varchar')
 */
class ValidDBData {

    /**
     * 数据实例
     */
    protected $objDB;

    /**
     * 字段信息缓存目录
     */
    protected $strColumnCacheDir = '';

    /**
     * 构造函数
     */
    public function __construct(DB $objDB) {
        $this->objDB = $objDB;
        $this->strColumnCacheDir = App::make('path.storage') . '/cache/column/';
    }

    /**
     * 获取表的字段信息
     */
    protected function getColumnInfo($strTableName) {
        //1.缓存文件获取
        $strFileName = $this->strColumnCacheDir . $strTableName . '.log';
        if (file_exists($strFileName)) {
            $arrColumnInfo = json_decode(file_get_contents($strFileName), true);
            if (is_array($arrColumnInfo) && !empty($arrColumnInfo)) {
                return $arrColumnInfo;
            }
        }

        //2.数据库获取
        $strSql = 'select col_name,cname,is_null,data_len,sql_type,data_type from ecolumn where table_name=:table_name';
        $arrParams = [':table_name' => $strTableName];
        $arrColumnInfo = $this->objDB->setMainTable('ecolumn')->select($strSql, $arrParams);
        if (!empty($arrColumnInfo)) {
            //写入缓存文件
            file_put_contents($strFileName, json_encode($arrColumnInfo));
            return $arrColumnInfo;
        }

        //3.没有获取返回空
        return [];
    }

    /**
     * 表中字段值检查
     * err_code说明<br>
     * 01:不能为空<br>
     * 02:类型错误<br>
     * 03:长度超过限制
     * @param string $strTableName 检查的字段的表名，如interview_invitepostpone
     * @param array $arrNeedCheck 需要检查的字段值，如["invite_id"=>1,"jobseek_id"=>1]
     * @param array $arrCheckCol 需要检查的字段，如果没有设置，默认检查$arrNeedCheck中的所有字段，如["invite_id"]
     * @param bool $blnCheckStrEmpty 字符串类型的字段，如果数据库标记为非空，是否需要检查字段值是否为空，默认为true
     * @return array $arrCheckResult 检查的结果，如[["col_name"=>"invite_id","cname"=>"面试邀请id","err_msg"=>"类型错误","err_code"=>"02"]]
     */
    public function check($strTableName, $arrNeedCheck = [], $arrCheckCol = [], $blnCheckStrEmpty = true) {
        $arrCheckResult = [];
        //没有需要检查数组
        if (!is_array($arrNeedCheck) || empty($arrNeedCheck)) {
            return $arrCheckResult;
        }
        //获取表中字段值
        $arrColumn = $this->getColumnInfo($strTableName);
        if (empty($arrColumn)) {
            return $arrCheckResult;
        }
        //将$arrColumn转换为$arrColumnHash
        $arrColumnHash = [];
        foreach ($arrColumn as $arrColumnTmp) {
            $arrColumnHash[$arrColumnTmp['col_name']] = $arrColumnTmp;
        }
        //开始检查
        $arrKey = array_unique(!empty($arrCheckCol) ? $arrCheckCol : array_keys($arrNeedCheck));
        foreach ($arrKey as $strCol) {
            $strColValue = isset($arrNeedCheck[$strCol]) ? $arrNeedCheck[$strCol] : '';
            $arrColumnTmp = $arrColumnHash[$strCol];
            if (empty($arrColumnTmp)) {
                continue;
            }
            //1.是否为空
            if ($arrColumnTmp['is_null'] == 0 && ($arrColumnTmp['data_type'] != 1 || ($arrColumnTmp['data_type'] == '1' && $blnCheckStrEmpty))) {
                if ($strColValue === '') {
                    $arrCheckResult[] = array("col_name" => $arrColumnTmp['col_name'], "cname" => $arrColumnTmp['cname'], "err_msg" => "不能为空", "err_code" => "01");
                    continue;
                }
            }
            //2.数据类型
            if (!empty($strColValue)) {
                switch ($arrColumnTmp['data_type']) {
                    case 2:
                        switch (strtolower($arrColumnTmp['sql_type'])) {
                            case "bit"://0,1
                            case "tinyint"://0-255
                            case "int"://2^31(-2,147,483,648)到2^31-1(2,147,483,647)
                            case "integer"://2^31(-2,147,483,648)到2^31-1(2,147,483,647)
                            case "smallint"://-2^15(-32,768)到2^15-1(32,767)
                            case "bigint"://-2^63(-9223372036854775808)到2^63-1(9223372036854775807)
                                if (!checkFormat($strColValue, Config::get('const.ValidFormat.FORMAT_INT'))) {
                                    $arrCheckResult[] = array("col_name" => $arrColumnTmp['col_name'], "cname" => $arrColumnTmp['cname'], "err_msg" => "类型错误", "err_code" => "02");
                                    continue;
                                }
                                break;
                            default:
                                if (!checkFormat($strColValue, Config::get('const.ValidFormat.FORMAT_DECIMAL'))) {
                                    $arrCheckResult[] = array("col_name" => $arrColumnTmp['col_name'], "cname" => $arrColumnTmp['cname'], "err_msg" => "类型错误", "err_code" => "02");
                                    continue;
                                }
                                break;
                        }
                        break;
                    case 3:
                        if (!checkFormat($strColValue, Config::get('const.ValidFormat.FORMAT_DATETIME'))) {
                            $arrCheckResult[] = array("col_name" => $arrColumnTmp['col_name'], "cname" => $arrColumnTmp['cname'], "err_msg" => "类型错误", "err_code" => "02");
                            continue;
                        }
                        break;
                }
            }
            //3.字符串类型，检查字段长度
            if (!empty($strColValue) && $arrColumnTmp['data_type'] == 1 && $arrColumnTmp['data_len'] != -1) {
                if (getStrLength($strColValue) > $arrColumnTmp['data_len']) {
                    $arrCheckResult[] = array("col_name" => $arrColumnTmp['col_name'], "cname" => $arrColumnTmp['cname'], "err_msg" => "长度超过限制", "err_code" => "03");
                    continue;
                }
            }
        }
        //返回检查结果
        return $arrCheckResult;
    }

}
