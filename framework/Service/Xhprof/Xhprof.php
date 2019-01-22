<?php

namespace Framework\Service\Xhprof;

use Framework\Service\Lib\Xhprof\Utils\XhprofRuns;

/**
 * https://xuanxuan2016.github.io/2018/09/20/xhprof/
 */
class Xhprof {

    /**
     * 构造函数
     */
    public function __construct() {
        //引入类库文件
        require_once BASE_PATH . '/framework/Service/Lib/Xhprof/Utils/xhprof_lib.php';
    }

    /**
     * 开始分析
     */
    public function start() {
        if (function_exists('xhprof_enable')) {
            xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_MEMORY);
        } else {
            error_log('Warning:Using xhprof for performance monitoring requires the xhprof extension to be turned on,see https://xuanxuan2016.github.io/2018/09/20/xhprof/');
        }
    }

    /**
     * 结束分析
     * @param string $strDir 分析文件存储位置，默认使用xhprof.output_dir配置
     * @param string $strType 分析类型，用于不同跟踪的区分
     */
    public function stop($strDir = '', $strType = 'xhprof') {
        if (function_exists('xhprof_disable')) {
            $mixXhprofData = xhprof_disable();
            $objXhprofRuns = new XhprofRuns($strDir);
            return $objXhprofRuns->save_run($mixXhprofData, $strType);
        } else {
            error_log('Warning:Using xhprof for performance monitoring requires the xhprof extension to be turned on,see https://xuanxuan2016.github.io/2018/09/20/xhprof/');
        }
    }

}
