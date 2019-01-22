<?php

namespace Framework\Service\Lib\Packer;

class CssPacker {

    private $strCss = '';

    /**
     * css压缩类
     */
    public function __construct($strCss = '') {
        $this->strCss = $strCss;
    }

    /**
     * 压缩
     */
    public function pack($strCss) {
        $this->strCss = $strCss;
        //去除注释
        //这里面的感叹号为封闭符号，这样里面的/就不需要转义了
        $this->strCss = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $this->strCss);
        //去除换行符，制表符，一些空格，等
        $this->strCss = str_replace(array("\r\n", "\r", "\n", "\t", '    '), '', $this->strCss);
        $this->strCss = str_replace(array(': ', ' :'), ':', $this->strCss);
        $this->strCss = str_replace(array(' {', '{ '), '{', $this->strCss);
        $this->strCss = str_replace(array(' }', '} '), '}', $this->strCss);
        $this->strCss = str_replace(array(' ,', ', '), ',', $this->strCss);
        $this->strCss = str_replace(array(' ;', '; '), ';', $this->strCss);
        //返回处理好的css
        return $this->strCss;
    }

}
