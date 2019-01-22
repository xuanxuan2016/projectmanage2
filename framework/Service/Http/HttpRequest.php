<?php

namespace Framework\Service\Http;

use Framework\Facade\Des;
use Framework\Contract\Http\Request as RequestContract;

/**
 * http请求
 */
class HttpRequest implements RequestContract {

    /**
     * get，post
     */
    protected $arrParam = [];

    /**
     * cookie
     */
    protected $arrCookie = [];

    /**
     * file
     */
    protected $arrFile = [];

    /**
     * 每次请求的唯一标识
     */
    protected $strRequestID = '';

    /**
     * 缓存的值
     */
    protected $arrCache = [];

    /**
     * 创建请求实例
     */
    public function __construct() {
        $this->init();
    }

    /**
     * 初始化数据
     */
    protected function init() {
        $this->arrParam = array_merge((array) filter_input_array(INPUT_GET), (array) filter_input_array(INPUT_POST), (array) json_decode(file_get_contents('php://input'), true));
        $this->arrCookie = filter_input_array(INPUT_COOKIE);
        $this->arrFile = $_FILES;
        $this->setRequestID(getGUID());
        $this->trimParam();
    }

    /**
     * 去除参数左右空格
     */
    protected function trimParam() {
        array_walk($this->arrParam, function(&$value) {
            $value = is_string($value) ? trim($value) : $value;
        });
    }

    /**
     * 获取单个参数
     * @param string $strParamName 参数名
     * @param string $strDefault 当获取不到参数时，返回的默认值
     */
    public function getParam($strParamName, $strDefault = '') {
        return isset($this->arrParam[$strParamName]) ? $this->arrParam[$strParamName] : $strDefault;
    }

    /**
     * 获取所有参数
     */
    public function getAllParam() {
        return $this->arrParam;
    }

    /**
     * 获取cookie
     * @param string $strCookieName cookie名
     */
    public function getCookie($strCookieName) {
        return Des::decrypt(isset($this->arrCookie[$strCookieName]) ? $this->arrCookie[$strCookieName] : '');
    }

    /**
     * 设置cookie
     * @param string $strCookieName cookie名称
     * @param string $strCookieValue cookie值
     * @param int $intExpire 超时时间，默认为会话结束
     * @param string $strDomain cookie的有效域名/子域名，默认当前域名
     * @param string $strPath cookie作用域，默认根目录
     */
    public function setCookie($strCookieName, $strCookieValue = '', $intExpire = 0, $strDomain = '', $strPath = '/') {
        $strCookieValue = Des::encrypt($strCookieValue);
        return setcookie($strCookieName, $strCookieValue, $intExpire, $strPath, $strDomain);
    }

    /**
     * 删除cookie
     * @param string $strCookieName cookie名称
     * @param string $strDomain cookie的有效域名/子域名，默认当前域名
     * @param string $strPath cookie作用域
     */
    public function delCookie($strCookieName, $strDomain = '', $strPath = '/') {
        return setcookie($strCookieName, '', time() - 1, $strPath, $strDomain);
    }

    /**
     * 获取uri
     */
    public function getUri() {
        if (isset($this->arrCache['uri'])) {
            return $this->arrCache['uri'];
        }
        $strUri = $_SERVER['REQUEST_URI'];
        $strUri = trim($strUri, '/');
        $strUri = strtolower(explode('?', $strUri)[0]);
        $this->arrCache['uri'] = $strUri;
        return $strUri;
    }

    /**
     * 判断是否ajax请求
     */
    public function isAjax() {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest") {
            return true;
        }
        return false;
    }

    /**
     * 获取请求的二级目录
     */
    public function getSecondDir() {
        if (isset($this->arrCache['second_dir'])) {
            return $this->arrCache['second_dir'];
        }
        $strSecondDir = strtolower(explode('/', $this->getUri())[0]);
        $this->arrCache['second_dir'] = $strSecondDir;
        return $strSecondDir;
    }

    /**
     * 获取请求的三级目录
     */
    public function getThirdDir() {
        if (isset($this->arrCache['third_dir'])) {
            return $this->arrCache['third_dir'];
        }
        $strThirdDir = strtolower(explode('/', $this->getUri())[1]);
        $this->arrCache['third_dir'] = $strThirdDir;
        return $strThirdDir;
    }

    /**
     * 获取客户端ip
     */
    public function getClientIP() {
        if (isset($this->arrCache['client_ip'])) {
            return $this->arrCache['client_ip'];
        }
        $strIP = '';
        $arrApacheRequest = array();
        if (function_exists('apache_request_headers')) {
            $arrApacheRequest = apache_request_headers();
        }
        if (isset($arrApacheRequest['ns_clientip'])) {
            //netscaler上面将客户端IP存储到了ns_clientip中
            $strIP = $arrApacheRequest['ns_clientip'];
        } else {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arrIP = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($arrIP as $strIP) {
                    $strIP = trim($strIP);
                    if ('unknown' != $strIP) {
                        break;
                    }
                }
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $strIP = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $strIP = $_SERVER['REMOTE_ADDR'];
            } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
                $strIP = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $strIP = getenv('HTTP_CLIENT_IP');
            } elseif (getenv('REMOTE_ADDR')) {
                $strIP = getenv('REMOTE_ADDR');
            }
        }
        $arrIP = explode(',', $strIP);
        $strIP = trim(empty($arrIP[0]) ? $strIP : $arrIP[0]);
        $this->arrCache['client_ip'] = $strIP;
        return $strIP;
    }

    /**
     * 获取服务端ip
     */
    public function getServerIP() {
        if (isset($this->arrCache['server_ip'])) {
            return $this->arrCache['server_ip'];
        }
        $strIP = '';
        if (isset($_SERVER)) {
            if (isset($_SERVER['SERVER_ADDR'])) {
                $strIP = $_SERVER['SERVER_ADDR'];
            } else if (isset($_SERVER['LOCAL_ADDR'])) {
                $strIP = $_SERVER['LOCAL_ADDR'];
            }
        } else {
            $strIP = getenv('SERVER_ADDR');
        }
        $this->arrCache['server_ip'] = $strIP;
        return $strIP;
    }

    /**
     * 获取请求标识id(guid)
     */
    public function setRequestID($strRequestID) {
        $this->strRequestID = $strRequestID;
    }

    /**
     * 获取请求标识id
     */
    public function getRequestID() {
        return $this->strRequestID;
    }

    /**
     * 跳转页面
     * @param string $strUrl 页面
     */
    public function redirect($strUrl) {
        header('Location:' . $strUrl);
        die();
    }

    /**
     * 是否手机浏览器
     */
    public function isMobile() {
        //如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            //找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        //判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = [
                'mobile', 'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg',
                'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod',
                'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm',
                'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap'
            ];
            //从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        //协议法，因为有可能不准确，放到最后判断
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            //如果只支持wml并且不支持html那一定是移动设备
            //如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

    /**
     * 是否微信浏览器
     */
    public function isWXBrowser() {
        $strUserAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($strUserAgent, 'micromessenger') !== false && strpos($strUserAgent, 'mobile') !== false) {
            return true;
        } else {
            return false;
        }
    }

}
