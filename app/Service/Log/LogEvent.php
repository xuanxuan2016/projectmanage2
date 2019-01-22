<?php

namespace App\Service\Log;

use Framework\Facade\App;
use Framework\Facade\User;
use Framework\Facade\Request;

/**
 * 埋点日志
 */
class LogEvent {

    /**
     * 日志类别：事件日志
     */
    const LOG_TYPE_EVENT = 'event';

    /**
     * 日志类别：页面访问日志
     */
    const LOG_TYPE_PAGEVIEW = 'pageView';

    /**
     * PageCode 字典
     */
    private $arrPageCode = array(
        //求职者
        'jobseek' => array(
            //home页
            'home_list' => '38101',
            //面试详情页
            'invite_info' => '38102'
        ),
        //面试官
        'interview' => array(
            //home页
            'home_list' => '38201',
            //面试详情页
            'invite_info' => '38202',
        )
    );

    /**
     * 事件类型字典
     */
    private $arrEventType = array(
        //求职者
        'jobseek' => array(
            'page_code' => '38101',
            //取消面试
            'interview_cancel' => 1,
            //接受面试
            'interview_receive' => 2,
            //申请改期
            'save_postpone' => 3,
            //评价
            'save_assess' => 4,
        ),
        //面试官
        'interview' => array(
            'page_code' => '38201',
            //确认改期
            'update_postpone' => 1,
            //直接改期
            'add_postpone' => 2,
            //邀请面试
            'invite_interview' => 3,
            //评价
            'save_assess' => 4,
        ),
        //hr转发
        'hrforward' => array(
            'page_code' => '38203',
            //评价简历
            'forward_assess' => 1,
            //转发简历
            'forward_resume' => 2,
        ),
        //接口
        'interface' => array(
            'page_code' => '38201',
            //确认改期
            'update_postpone' => 1,
            //直接改期
            'add_postpone' => 2,
            //评价
            'save_assess' => 4,
        ),
        //消息
        'msg' => array(
            'page_code' => 0,
            //成功
            'success' => 5001,
            //失败
            'fail' => 5002,
        ),
        //面试邀请(从interview分离出来)
        'add_invite' => array(
            'page_code' => 0,
            //成功
            'success' => 5003,
        )
    );

    /**
     * pageCode
     */
    private $strPageCode = '';

    /**
     * 事件类型
     */
    private $strEventType = '';

    /**
     * 角色
     */
    private $strRole = '';

    /**
     * 来源
     */
    private $strCustomerParam = '';

    /**
     * 日志目录
     */
    private $strLogDir = '';

    /**
     * 分隔符
     */
    private $strSep;

    /**
     * 自定义分隔符
     */
    private $strSepCustomer;

    /**
     * 版本类型
     */
    private $strVerType = '1';

    /**
     * pageview版本类型
     */
    private $strPageViewVerType = '3';

    /**
     * 网站id
     */
    private $strWebId = '5';

    /**
     * 构造方法
     */
    public function __construct() {
        $this->strLogDir = App::make('path.storage') . '/hrologs/';
        $this->strSep = chr(26);
        $this->strSepCustomer = chr(22);
    }

    /**
     * 记录日志
     * @param string $strLogType 日志类别，LOG_TYPE_*
     * @param string $strKey 角色信息|操作|来源 如： interview|save_assess|02 
     * <br /> $strKey中角色信息和操作必填；统计PageView不需要来源；统计事件类型时，有来源则填写来源，无来源则不填来源。
     * @param array $arrParam 页面参数，若不空，则使用页面参数
     */
    public function log($strLogType, $strKey, $arrParam = []) {
        //获取埋点的编码信息
        if (!$this->getCodeInfo($strLogType, $strKey)) {
            return;
        }
        //创建日志目录
        if (!$this->createDir()) {
            return;
        }
        if (($strFileName = $this->getFileName($strLogType)) == '') {
            return;
        }
        //获取页面参数信息
        $strContent = '';
        if (!$this->getLogContent($strLogType, $arrParam, $strContent)) {
            return;
        }
        //写数据
        if (!file_exists($strFileName)) {
            //如果文件未存在直接写
            file_put_contents("{$strFileName}", "{$strContent}", FILE_APPEND | LOCK_EX);
            @chmod($strFileName, 0775);
        } else {
            //如果文件已存在，判断是否可写
            if (is_writable($strFileName)) {
                file_put_contents("{$strFileName}", "{$strContent}", FILE_APPEND | LOCK_EX);
            }
        }
    }

    /**
     * 获取日志内容
     */
    private function getLogContent($strLogType, $arrParam, &$strContent) {
        $blnRet = true;
        //面试官id
        $strLoginAccountId = $this->getLoginAccountID();
        if (array_key_exists('account_id', $arrParam)) {
            $strLoginAccountId = $arrParam['account_id'];
        }
        if (empty($strLoginAccountId)) {
            $blnRet = false;
        }
        //日志字段
        $arrContent = [];
        switch ($strLogType) {
            case self::LOG_TYPE_EVENT:
                $arrContent = [
                    'VerType1' => $this->strVerType,
                    'webId' => $this->strWebId,
                    'logTime' => $this->curMicroTime(),
                    'domain' => '',
                    'pageCode' => $this->strPageCode,
                    'userId' => $strLoginAccountId,
                    'guid' => $strLoginAccountId,
                    'ip' => Request::getClientIP(),
                    'eventType' => $this->strEventType,
                    'traceName' => '',
                    'VerType2' => $this->strVerType
                ];
                $strContent = join($this->strSep, $arrContent) . $this->strSepCustomer . $this->strCustomerParam . "\r\n";
                break;
            case self::LOG_TYPE_PAGEVIEW:
                $arrContent = [
                    'VerType' => $this->strPageViewVerType,
                    'webId' => $this->strWebId,
                    'logTime' => $this->curMicroTime(),
                    'userId' => $strLoginAccountId,
                    'guid' => $strLoginAccountId,
                    'ip' => Request::getClientIP(),
                    'refUrl' => '',
                    'url' => '',
                    'pageCode' => $this->strPageCode,
                    'cd' => '',
                    'la' => '',
                    'sc' => '',
                    'ug' => '',
                    'cusParam' => ''
                ];
                $strContent = join($this->strSep, $arrContent) . "\r\n";
                break;
        }

        return $blnRet;
    }

    /**
     * 获取埋点的编码信息
     */
    private function getCodeInfo($strLogType, $strKey) {
        $blnRet = true;
        switch ($strLogType) {
            case self::LOG_TYPE_PAGEVIEW:
                $arrPageCode = $this->arrPageCode;
                $arrKey = explode('|', $strKey);
                if (empty($arrPageCode[$arrKey[0]][$arrKey[1]])) {
                    $blnRet = false;
                }
                $this->strRole = $arrKey[0];
                $this->strPageCode = $arrPageCode[$arrKey[0]][$arrKey[1]];
                break;
            case self::LOG_TYPE_EVENT:
                $arrEventType = $this->arrEventType;
                $arrKey = explode('|', $strKey);
                if (empty($arrEventType[$arrKey[0]][$arrKey[1]])) {
                    $blnRet = false;
                }
                $this->strRole = $arrKey[0];
                $this->strPageCode = $arrEventType[$arrKey[0]]['page_code'];
                $this->strEventType = $arrEventType[$arrKey[0]][$arrKey[1]];
                $this->strCustomerParam = count($arrKey) == 3 ? $arrKey[2] : '';
                break;
            default :
                $blnRet = false;
                break;
        }
        return $blnRet;
    }

    /**
     * 获取文件名
     */
    private function getFileName($strLogType) {
        $strLogDir = $this->strLogDir . date('Ym') . '/';
        $arrHostName = explode('.', getenv('HOSTNAME'));
        return $strLogDir . $arrHostName[0] . '.' . date('YmdH') . '.' . $strLogType . '.log';
    }

    /**
     * 创建日志目录
     */
    private function createDir() {
        $strLogDir = $this->strLogDir . date('Ym');
        if (!is_dir($strLogDir)) {
            if (@mkdir($strLogDir, 0777, true)) {
                @chmod($strLogDir, 0775);
                return true;
            }
        } else {
            return true;
        }
        return false;
    }

    /**
     * 获取登录账号id
     */
    private function getLoginAccountID() {
        return User::getAccountId();
    }

    /**
     * 获取当前带毫秒的时间
     */
    private function curMicroTime() {
        return getMicroTimeStamp();
    }

}
