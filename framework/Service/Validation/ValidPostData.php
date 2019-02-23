<?php

namespace Framework\Service\Validation;

use Framework\Facade\Log;
use Framework\Facade\Config;

/**
 * 字段自定义配置检查
 */
class ValidPostData {

    /**
     * 构造函数
     */
    public function __construct() {
        
    }

    /**
     * 字段检查
     * @param array $arrNeedCheck 需要检查的字段值，如["invite_id"=>1,"jobseek_id"=>1]
     * @param array $arrCheckCol 需要检查的字段，如果没有设置，默认检查$arrNeedCheck中的所有字段，如["invite_id"]
     * @param array $arrRules 检查规则
     * @return array $arrCheckResult 检查的结果，如[["col_name"=>"invite_id","err_msg"=>["错误信息1","错误信息2"]
     */
    public function check(&$arrNeedCheck = [], $arrCheckCol = [], $arrRules = []) {
        //1.需要检查的列
        $arrCheckCol = array_unique(!empty($arrCheckCol) ? $arrCheckCol : array_keys($arrNeedCheck));
        //2.循环检查
        $arrCheckResult = [];
        foreach ($arrCheckCol as $strCol) {
            if (!isset($arrRules[$strCol])) {
                //无校验规则
                continue;
            }
            if (!isset($arrNeedCheck[$strCol])) {
                //无字段值
                $arrCheckResult[] = "{$strCol}未设置值";
                continue;
            }
            $arrNeedCheck[$strCol] = $this->checkCol($arrNeedCheck[$strCol], $arrRules[$strCol], $arrCheckResult);
        }
        //3.返回检查结果
        return $arrCheckResult;
    }

    /**
     * 单个字段检查
     */
    protected function checkCol($mixValue, $arrRule, &$arrCheckResult) {
        $arrRuleKey = array_keys($arrRule);
        //trim
        if (in_array('trim', $arrRuleKey)) {
            $mixValue = $this->trim($mixValue);
            unset($arrRuleKey[array_search('trim', $arrRuleKey)]);
        }
        //其它check，循环处理
        foreach ($arrRuleKey as $strRuleKey) {
            if (method_exists($this, $strRuleKey)) {
                if (!$this->$strRuleKey($mixValue, $arrRule[$strRuleKey])) {
                    $arrCheckResult[] = $arrRule[$strRuleKey]['err_msg'];
                    break;
                }
            }
        }
        //返回值
        return $mixValue;
    }

    /**
     * trim
     */
    protected function trim($mixValue) {
        return trim($mixValue);
    }

    /**
     * required
     */
    protected function required($mixValue, $mixRule) {
        return $mixRule['value'] === false ? true : $mixValue !== '';
    }

    /**
     * optional
     */
    protected function optional($mixValue, $mixRule) {
        return in_array($mixValue, $mixRule['value']);
    }

    /**
     * min_len
     */
    protected function min_len($mixValue, $mixRule) {
        return getStrLength($mixValue) < $mixRule['value'] ? false : true;
    }

    /**
     * max_len
     */
    protected function max_len($mixValue, $mixRule) {
        return getStrLength($mixValue) > $mixRule['value'] ? false : true;
    }

    /**
     * type
     */
    protected function type($mixValue, $mixRule) {
        switch ($mixRule['value']) {
            case 'int':
                return checkFormat($mixValue, Config::get('const.ValidFormat.FORMAT_INT'));
                break;
            case 'posint':
                return checkFormat($mixValue, Config::get('const.ValidFormat.FORMAT_POSINT'));
                break;
            case 'email':
                return checkFormat($mixValue, Config::get('const.ValidFormat.FORMAT_EMAIL'));
                break;
            case 'date':
                return checkFormat($mixValue, Config::get('const.ValidFormat.FORMAT_DATETIME'));
                break;
            default :
                return true;
        }
    }

    /**
     * validator
     * 自定义验证函数
     */
    protected function validator($mixValue, $mixRule) {
        return is_callable($mixRule['value']) ? $mixRule['value']($mixValue) : true;
    }

}
