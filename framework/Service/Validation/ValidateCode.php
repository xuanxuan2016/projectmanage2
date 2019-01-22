<?php

namespace Framework\Service\Validation;

use Exception;
use Framework\Facade\App;

/**
 * 验证码类
 */
class ValidateCode {

    /**
     * 随机因子
     */
    protected $strCharset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';

    /**
     * 验证码
     */
    protected $strCode;

    /**
     * 验证码长度
     */
    protected $intCodeLen = 4;

    /**
     * 图片宽度
     */
    protected $intWidth = 100;

    /**
     * 图片高度
     */
    protected $intHeight = 40;

    /**
     * 图形资源句柄
     */
    protected $objImg;

    /**
     * 指定的字体
     */
    protected $strFontPath;

    /**
     * 指定字体大小
     */
    protected $intFontSize = 20;

    /**
     * 指定字体颜色
     */
    protected $intFontColor;

    /**
     * 构造方法
     */
    public function __construct() {
        $this->strFontPath = App::make('path.resource') . '/ttl/Elephant.ttf';
    }

    /**
     * 生成随机码
     */
    protected function createCode() {
        if (!empty($this->strCode)) {
            return;
        }
        $intCharsetLen = strlen($this->strCharset) - 1;
        for ($i = 0; $i < $this->intCodeLen; $i++) {
            $this->strCode .= $this->strCharset[mt_rand(0, $intCharsetLen)];
        }
    }

    /**
     * 生成背景
     */
    protected function createBg() {
        $this->objImg = imagecreatetruecolor($this->intWidth, $this->intHeight);
        $intColor = imagecolorallocate($this->objImg, mt_rand(157, 255), mt_rand(157, 255), mt_rand(157, 255));
        if (!imagefilledrectangle($this->objImg, 0, $this->intHeight, $this->intWidth, 0, $intColor)) {
            throw new Exception("验证码生成背景失败！");
        }
    }

    /**
     * 生成字体
     */
    protected function createFont() {
        $intX = $this->intWidth / $this->intCodeLen;
        for ($i = 0; $i < $this->intCodeLen; $i++) {
            $this->intFontColor = imagecolorallocate($this->objImg, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            if (!imagettftext($this->objImg, $this->intFontSize, mt_rand(-30, 30), $intX * $i + mt_rand(1, 5), $this->intHeight / 1.4, $this->intFontColor, $this->strFontPath, $this->strCode[$i])) {
                throw new Exception("验证码生成文字失败！字体路径：" . $this->strFontPath);
            }
        }
    }

    /**
     * 生成线条、雪花
     */
    protected function createLine() {
        //线条
        for ($i = 0; $i < 6; $i++) {
            $intColor = imagecolorallocate($this->objImg, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            if (!imageline($this->objImg, mt_rand(0, $this->intWidth), mt_rand(0, $this->intHeight), mt_rand(0, $this->intWidth), mt_rand(0, $this->intHeight), $intColor)) {
                throw new Exception("验证码生成线条失败！");
            }
        }
        //雪花
        for ($i = 0; $i < 100; $i++) {
            $intColor = imagecolorallocate($this->objImg, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
            if (!imagestring($this->objImg, mt_rand(1, 5), mt_rand(0, $this->intWidth), mt_rand(0, $this->intHeight), '*', $intColor)) {
                throw new Exception("验证码生成雪花失败！");
            }
        }
    }

    /**
     * 设置图片字符
     * 不设置的话，自动生成4位的字符
     */
    public function setCode($strCode) {
        $this->strCode = $strCode;
    }

    /**
     * 获取验证码(图片编码)
     */
    public function getOutPut() {
        try {
            //将图片输出缓存起来
            ob_start(); //Let's start output buffering.
            if (!imagepng($this->objImg)) { //This will normally output the image, but because of ob_start(), it won't.
                throw new Exception("验证码输出失败！");
            }
            imagedestroy($this->objImg);
            $strContents = ob_get_contents(); //Instead, output above is saved to $strContents
            ob_end_clean(); //End the output buffer.
            //输出base64编码的图片
            return base64_encode($strContents);
        } catch (Exception $ex) {
            return '';
        }
    }

    /**
     * 创建图片
     */
    public function createImg() {
        try {
            $this->createBg();
            $this->createCode();
            $this->createLine();
            $this->createFont();
        } catch (Exception $ex) {
            return '';
        }
    }

    /**
     * 获取验证码字符串
     */
    public function getCode() {
        return strtolower($this->strCode);
    }

}
