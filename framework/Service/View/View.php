<?php

namespace Framework\Service\View;

use Framework\Facade\Config;
use Framework\Facade\Request;
use Framework\Service\Lib\Packer\JsPacker;
use Framework\Service\Lib\Packer\CssPacker;
use Framework\Service\Foundation\Application;
use Framework\Service\Exception\ControllerException;

/**
 * 视图
 * 1.可根据是否手机浏览器，来加载mobile的模板，如果没有默认用pc的模板
 */
class View {

    /**
     * 应用实例
     */
    protected $objApp;

    /**
     * js压缩实例
     */
    protected $objJsPacker;

    /**
     * css压缩实例
     */
    protected $objCssPacker;

    /**
     * 创建视图实例
     */
    public function __construct(Application $objApp, JsPacker $objJsPacker, CssPacker $objCssPacker) {
        $this->objApp = $objApp;
        $this->objJsPacker = $objJsPacker;
        $this->objCssPacker = $objCssPacker;
    }

    /**
     * 生成视图
     */
    public function make($arrData) {
        //获取当前视图内容
        $strViewPath = $this->getViewPath();
        $strView = file_get_contents($strViewPath);

        //获取视图的模板内容
        $strViewTlp = '';
        $arrDataTlp = [];
        $this->getTemplate($arrData['template'], $strViewTlp, $arrDataTlp);

        //合并数据
        $arrData = array_merge_recursive($arrDataTlp, $arrData);
        if (!empty($strViewTlp)) {
            $strView = str_replace('{{template}}', $strView, $strViewTlp);
        }

        //内容,js,css
        $this->resolveContent($strView, $arrData['content']);
        $this->resolveCss($strView, $arrData['css']);
        $this->resolveJs($strView, $arrData['js']);

        //返回解析后的视图
        return $strView;
    }

    /**
     * 获取模板内容
     */
    protected function getTemplate($arrTemplate, &$strViewTlp, &$arrDataTlp) {
        if (isset($arrTemplate['controller'])) {
            $objTemplateController = $this->objApp->make($arrTemplate['controller']);
            $arrDataTlp = $objTemplateController->getViewData();
        }

        if (isset($arrTemplate['view'])) {
            $strTemplatePath = $this->objApp->make('path.resource') . '/view/' . $arrTemplate['view'] . '.view.html';
            $strViewTlp = file_exists($strTemplatePath) ? file_get_contents($strTemplatePath) : '';
        }
    }

    /**
     * 解析内容
     */
    protected function resolveContent(&$strView, $arrContent) {
        $strReg = '/({{\s*([a-z_]+)\s*}})/i';
        preg_match_all($strReg, $strView, $arrMatch, PREG_PATTERN_ORDER);
        if (!empty($arrMatch[0])) {
            for ($i = 0, $j = count($arrMatch[1]); $i < $j; $i++) {
                $strView = str_replace($arrMatch[1][$i], isset($arrContent[$arrMatch[2][$i]]) ? $arrContent[$arrMatch[2][$i]] : $arrMatch[1][$i], $strView);
            }
        }
    }

    /**
     * 解析css
     */
    protected function resolveCss(&$strView, $arrCss) {
        foreach ($arrCss as $arrCssTmp) {
            $strSearch = '</head>';
            //是否设置路径
            if (!isset($arrCssTmp['path'])) {
                continue;
            }
            //远程文件直接加载
            if (isset($arrCssTmp['is_remote']) && $arrCssTmp['is_remote'] == 1) {
                $strRef = sprintf('<link href="%s" rel="stylesheet">', $arrCssTmp['path']);
                $strView = str_replace($strSearch, $strRef . $strSearch, $strView);
                continue;
            }

            if (isset($arrCssTmp['is_pack']) && $arrCssTmp['is_pack'] == 0) {
                //本地文件，不用压缩
                $strPath = $arrCssTmp['path'];
            } else {
                //本地文件，需要压缩
                $strPath = $this->getPackCss($arrCssTmp['path']);
            }
            $strVersion = $this->getFileChangeTime(2, $strPath);
            $strRef = sprintf('<link href="%s%s?version=%s" rel="stylesheet">', Config::get('web.css.domain'), $strPath, $strVersion);
            $strView = str_replace($strSearch, $strRef . $strSearch, $strView);
        }
    }

    /**
     * 获取压缩的Css文件
     */
    protected function getPackCss($strCssPath) {
        //获取压缩css的目录
        $strPackCssDir = $this->getPackCssDir($strCssPath);
        if (empty($strPackCssDir)) {
            return $strCssPath;
        }
        $strPackCssPath = $this->objApp->make('path.web') . '/css/packer/' . str_replace('.css', '.min.css', $strCssPath);
        if (is_file($strPackCssPath)) {
            unlink($strPackCssPath);
        }

        //生成压缩文件        
        if (Config::get('web.css.read_only') == 0) {
            $strOriCss = file_get_contents($this->objApp->make('path.web') . '/css/' . $strCssPath);
            $strPackCss = $this->objCssPacker->pack($strOriCss);
            file_put_contents($strPackCssPath, $strPackCss, LOCK_EX);
        }

        //如果压缩文件不存在则加载非压缩文件
        if (file_exists($strPackCssPath)) {
            return 'packer/' . str_replace('.css', '.min.css', $strCssPath);
        } else {
            return $strCssPath;
        }
    }

    /**
     * 创建存放压缩css目录
     */
    protected function getPackCssDir($strCssPath) {
        $arrPath = explode('/', $strCssPath);
        unset($arrPath[count($arrPath) - 1]);
        $strDir = $this->objApp->make('path.web') . '/css/packer/' . implode('/', $arrPath);
        if (!is_dir($strDir)) {
            if (mkdir($strDir, 0777, true)) {
                return $strDir;
            } else {
                return '';
            }
        } else {
            return $strDir;
        }
    }

    /**
     * 解析js
     */
    protected function resolveJs(&$strView, $arrJs) {
        foreach ($arrJs as $arrJsTmp) {
            $strSearch = isset($arrJsTmp['is_addhead']) && $arrJsTmp['is_addhead'] == 1 ? '</head>' : '</body>';
            //是否设置路径
            if (!isset($arrJsTmp['path'])) {
                continue;
            }
            //远程文件直接加载
            if (isset($arrJsTmp['is_remote']) && $arrJsTmp['is_remote'] == 1) {
                $strRef = sprintf('<script src="%s"></script>', $arrJsTmp['path']);
                $strView = str_replace($strSearch, $strRef . $strSearch, $strView);
                continue;
            }

            if (isset($arrJsTmp['is_pack']) && $arrJsTmp['is_pack'] == 0) {
                //本地文件，不用压缩
                $strPath = $arrJsTmp['path'];
            } else {
                //本地文件，需要压缩
                $strPath = $this->getPackJs($arrJsTmp['path']);
            }
            $strVersion = $this->getFileChangeTime(1, $strPath);
            $strRef = sprintf('<script src="%s%s?version=%s"></script>', Config::get('web.js.domain'), $strPath, $strVersion);
            $strView = str_replace($strSearch, $strRef . $strSearch, $strView);
        }
    }

    /**
     * 获取压缩的Js文件
     */
    protected function getPackJs($strJsPath) {
        //获取压缩js的目录
        $strPackJsDir = $this->getPackJsDir($strJsPath);
        if (empty($strPackJsDir)) {
            return $strJsPath;
        }
        $strPackJsPath = $this->objApp->make('path.web') . '/js/packer/' . str_replace('.js', '.min.js', $strJsPath);
        if (is_file($strPackJsPath)) {
            unlink($strPackJsPath);
        }

        //生成压缩文件        
        if (Config::get('web.js.read_only') == 0) {
            $strOriJs = file_get_contents($this->objApp->make('path.web') . '/js/' . $strJsPath);
            $this->objJsPacker->init($strOriJs);
            $strPackJs = $this->objJsPacker->pack();
            file_put_contents($strPackJsPath, $strPackJs);
        }

        //如果压缩文件不存在则加载非压缩文件
        if (file_exists($strPackJsPath)) {
            return 'packer/' . str_replace('.js', '.min.js', $strJsPath);
        } else {
            return $strJsPath;
        }
    }

    /**
     * 创建存放压缩js目录
     */
    protected function getPackJsDir($strJsPath) {
        $arrPath = explode('/', $strJsPath);
        unset($arrPath[count($arrPath) - 1]);
        $strDir = $this->objApp->make('path.web') . '/js/packer/' . implode('/', $arrPath);
        if (!is_dir($strDir)) {
            if (mkdir($strDir, 0777, true)) {
                return $strDir;
            } else {
                return '';
            }
        } else {
            return $strDir;
        }
    }

    /**
     * 获取文件修改时间
     * @param int $intType 文件类型，1:js 2:css
     * @param string $strPath 文件路径
     */
    protected function getFileChangeTime($intType = 1, $strPath) {
        $strDir = $this->objApp->make('path.web') . ($intType == 1 ? '/js/' : '/css/');
        $strFilePath = $strDir . $strPath;
        return file_exists($strFilePath) ? filemtime($strFilePath) : time();
    }

    /**
     * 获取视图文件
     */
    protected function getViewPath() {
        //配置查找
        $strView = $this->getViewPathConfig();
        if (!empty($strView)) {
            return $strView;
        }

        //文件查找
        $strView = $this->getViewPathFile();
        if (!empty($strView)) {
            return $strView;
        }

        //抛出异常，需要重定向
        throw new ControllerException('请求视图错误');
    }

    /**
     * 从配置中获取
     */
    protected function getViewPathConfig() {
        $strViewPath = Config::get('app.view.' . Request::getUri());
        if (!empty($strViewPath)) {
            $strFilePath = $this->objApp->make('path.resource') . '/view/' . $strViewPath . '.view.html';
            if (is_file($strFilePath)) {
                return $strFilePath;
            }
        }
        return '';
    }

    /**
     * 从文件中获取
     */
    protected function getViewPathFile() {
        $strUri = Request::getUri();
        if (!in_array(Request::getSecondDir(), Config::get('app.second_dir'))) {
            $strUri = 'web/' . $strUri;
        }
        $strFilePath = $this->objApp->make('path.resource') . '/view/' . $strUri . '.view.html';
        if (is_file($strFilePath)) {
            return $strFilePath;
        }
        return '';
    }

}
