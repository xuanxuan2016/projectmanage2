<?php

namespace App\Http\Model\Web\Common;

use Framework\Facade\App;
use Framework\Facade\Request;
use Framework\Service\Database\DB;
use Framework\Service\MarkDown\HyperDown;
use Framework\Service\Validation\ValidPostData;

class ProjectDocModel {

    /**
     * 数据实例
     */
    protected $objDB;

    /**
     * HyperDown实例
     */
    protected $objHyperDown;

    /**
     * post参数校验配置
     */
    protected $arrRules = [
        'id' => [
            'type' => ['value' => 'posint', 'err_msg' => 'id格式不正确']
        ]
    ];

    /**
     * 构造方法
     */
    public function __construct(DB $objDB, HyperDown $objHyperDown, ValidPostData $objValidPostData) {
        $this->objDB = $objDB;
        $this->objHyperDown = $objHyperDown;
        $this->objValidPostData = $objValidPostData;
    }

    // -------------------------------------- loadList -------------------------------------- //

    /**
     * 获取列表数据
     */
    public function loadList(&$strErrMsg, &$arrData) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkLoadList($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $arrArticle = $this->getArticleList($arrParam);
        //4.结果返回
        $arrData = $arrArticle;
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkLoadList(&$arrParam) {
        //1.获取页面参数
        //2.字段自定义配置检查
        //3.字段数据库配置检查
        //4.业务检查
        //5.其它参数
    }

    /**
     * 获取数据
     */
    protected function getArticleList($arrParam) {
        //查询
        $strSql = "select a.id,a.cname
                    from article a
                    where 1=1 and a.type=:type and  a.status=:status
                    order by a.list_index";
        $arrParams = [
            ':type' => 'projectdoc',
            ':status' => '01'
        ];
        $arrArticle = $this->objDB->setMainTable('article')->select($strSql, $arrParams);

        //返回
        return [
            'list' => $arrArticle
        ];
    }

    // -------------------------------------- loadProjectDocInfo -------------------------------------- //

    /**
     * 加载信息
     */
    public function loadProjectDocInfo(&$strErrMsg, &$arrData) {
        $arrParam = [];
        //1.参数验证
        $strErrMsg = $this->checkLoadProjectDocInfo($arrParam);
        if (!empty($strErrMsg)) {
            return false;
        }
        //2.记录操作日志(埋点)
        //3.业务逻辑
        $strArticle = $this->getProjectDocInfo($arrParam);
        //4.结果返回
        $arrData['info'] = $strArticle;
        return true;
    }

    /**
     * 参数检查
     */
    protected function checkLoadProjectDocInfo(&$arrParam) {
        //1.获取页面参数
        $arrParam = [
            'id' => Request::getParam('id')
        ];

        //2.字段自定义配置检查
        $arrErrMsg = $this->objValidPostData->check($arrParam, ['id'], $this->arrRules);
        if (!empty($arrErrMsg)) {
            return join(';', $arrErrMsg);
        }
        //3.字段数据库配置检查
        //4.业务检查
    }

    /**
     * 获取数据
     */
    protected function getProjectDocInfo($arrParam) {
        //查询
        $strSql = 'select a.cname
                    from article a
                    where a.id=:id';
        $arrParams[':id'] = $arrParam['id'];
        $arrArticleInfo = $this->objDB->setMainTable('article')->select($strSql, $arrParams);

        //返回
        $strFilePath = App::make('path.resource') . "/markdown/projectdoc/{$arrArticleInfo[0]['cname']}.md";
        return $this->objHyperDown->makeHtml(file_get_contents($strFilePath));
    }

}
