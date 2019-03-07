<?php

namespace Framework\Service\File\Excel;

use Exception;
use Framework\Facade\App;
use Framework\Facade\Log;
use Framework\Facade\File;
use Framework\Facade\Config;
use PHPExcel;
use PHPExcel_Cell;
use PHPExcel_Style;
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Cell_DataType;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_NumberFormat;

/**
 * ExcelWrite类
 */
class ExcelWrite {

    /**
     * Excel操作对象
     */
    protected $objExcel;

    /**
     * Sheet操作对象
     */
    protected $objSheet;

    /**
     * 生成Sheet的属性
     */
    protected $objSheetAtt = [
        'header_row_start' => 1, //表头开始行
        'header_row_count' => 1, //表头行数
        'header_column_start' => 0, //表头开始列    
        'row_count' => 0, //实际数据行数
        'column_count' => 0, //实际数据列数
        'sheet_option' => []  //sheet属性
    ];

    /**
     * 构造函数属性
     */
    protected $arrInit = [];

    /**
     * 列宽
     */
    protected $arrColumnWidth = [];

    /**
     * sheet默认属性
     */
    protected $arrDefaultOption = [
        /**
         * 标题
         */
        'head' => [
            /**
             * 背景色，默认淡灰色
             */
            'background_color' => '#d9d9d9',
            /**
             * 字体色，默认黑色
             */
            'font_color' => '#000000',
            /**
             * 边框色，默认黑色
             */
            'border_color' => '#000000',
            /**
             * 标题是否加粗，默认是
             */
            'bold' => true
        ],
        /**
         * 内容
         */
        'body' => [
            /**
             * 是否写具体数据，默认是
             */
            'write_detail_data' => true,
            /**
             * 背景色，默认白色
             */
            'background_color' => '#FFFFFF',
            /**
             * 字体色，默认黑色
             */
            'font_color' => '#000000',
            /**
             * 边框色，默认黑色
             */
            'border_color' => '#000000'
        ],
        /**
         * 锁定
         */
        'freeze' => [
            /**
             * 是否需要锁定，默认需要锁定
             */
            'freezeable' => true,
            /**
             * 锁定行，默认为内容开始行
             */
            'row' => 0,
            /**
             * 锁定列，默认为内容开始列
             */
            'column' => 0
        ]
    ];

    /**
     * 构造函数
     */
    public function __construct() {
        //加载phpexcel
        include_once App::make('path.framework') . '/Service/Lib/PHPExcel/PHPExcel.php';
    }

    /**
     * 初始化类
     * @param string $strCname 文件名
     * @param bool $blnDownDel 文件下载后是否删除
     */
    public function init($strCname = '导出文件', $blnDownDel = true) {
        $this->objExcel = new PHPExcel();
        $this->arrInit['cname'] = $strCname;
        $this->arrInit['down_del'] = $blnDownDel ? 1 : 0;
        return $this;
    }

    /**
     * 获取生成附件地址
     */
    protected function getAttachPath() {
        $strAttPath = App::make('path.storage') . '/cache/file/' . 'download/';
        return File::getDirPath($strAttPath);
    }

    /**
     * 获取sheet的样式-表头
     */
    protected function getSheetStyleHeader() {
        $arrSheetStyle = new PHPExcel_Style();
        $arrSheetStyle->applyFromArray(
                [
                    'fill' => [
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => ['argb' => str_replace('#', 'FF', $this->objSheetAtt['sheet_option']['head']['background_color'])]
                    ],
                    'borders' => [
                        'top' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['argb' => str_replace('#', 'FF', $this->objSheetAtt['sheet_option']['head']['border_color'])]],
                        'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['argb' => str_replace('#', 'FF', $this->objSheetAtt['sheet_option']['head']['border_color'])]],
                        'bottom' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['argb' => str_replace('#', 'FF', $this->objSheetAtt['sheet_option']['head']['border_color'])]],
                        'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['argb' => str_replace('#', 'FF', $this->objSheetAtt['sheet_option']['head']['border_color'])]]
                    ],
                    'font' => [
                        'name' => 'Arial',
                        'size' => '9',
                        'bold' => $this->objSheetAtt['sheet_option']['head']['bold'],
                        'color' => ['argb' => str_replace('#', 'FF', $this->objSheetAtt['sheet_option']['head']['font_color'])]
                    ],
                    'numberformat' => [
                        'code' => PHPExcel_Style_NumberFormat::FORMAT_TEXT
                    ],
                    'alignment' => [
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'shrinkToFit' => true
                    ]
                ]
        );
        return $arrSheetStyle;
    }

    /**
     * 获取sheet的样式-具体数据
     */
    protected function getSheetStyleDetail() {
        $arrSheetStyle = new PHPExcel_Style();
        $arrSheetStyle->applyFromArray(
                [
                    'fill' => [
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => ['argb' => str_replace('#', 'FF', $this->objSheetAtt['sheet_option']['body']['background_color'])]
                    ],
                    'borders' => [
                        'top' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['argb' => str_replace('#', 'FF', $this->objSheetAtt['sheet_option']['body']['border_color'])]],
                        'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['argb' => str_replace('#', 'FF', $this->objSheetAtt['sheet_option']['body']['border_color'])]],
                        'bottom' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['argb' => str_replace('#', 'FF', $this->objSheetAtt['sheet_option']['body']['border_color'])]],
                        'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['argb' => str_replace('#', 'FF', $this->objSheetAtt['sheet_option']['body']['border_color'])]]
                    ],
                    'font' => [
                        'name' => 'Arial',
                        'size' => '9',
                        'color' => ['argb' => str_replace('#', 'FF', $this->objSheetAtt['sheet_option']['body']['font_color'])]
                    ],
                    'numberformat' => [
                        'code' => PHPExcel_Style_NumberFormat::FORMAT_TEXT
                    ],
                    'alignment' => [
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                    ]
                ]
        );
        return $arrSheetStyle;
    }

    /**
     * 设置sheet属性
     * @param array $arrOption
     */
    protected function setSheetOption($arrOption) {
        foreach ($this->arrDefaultOption as $key => $value) {
            if (isset($arrOption[$key])) {
                $this->objSheetAtt['sheet_option'][$key] = array_merge($value, $arrOption[$key]);
            } else {
                $this->objSheetAtt['sheet_option'][$key] = $value;
            }
        }
    }

    /**
     * 根据列数获取excel列名
     * @param int $intIndex 列数
     */
    protected function getColumnLetter($intIndex) {
        return PHPExcel_Cell::stringFromColumnIndex($intIndex);
    }

    /**
     * 设置sheet样式
     */
    protected function setSheetStyle() {
        //单元格样式
        //标题行：如A1:AI1
        $this->objSheet->setSharedStyle($this->getSheetStyleHeader(), sprintf('%s%s:%s%s', $this->getColumnLetter($this->objSheetAtt['header_column_start']), $this->objSheetAtt['header_row_start'], $this->getColumnLetter($this->objSheetAtt['header_column_start'] + $this->objSheetAtt['column_count'] - 1), $this->objSheetAtt['header_row_start'] + $this->objSheetAtt['header_row_count'] - 1));
        //数据行：如A2:AI150
        $this->objSheet->setSharedStyle($this->getSheetStyleDetail(), sprintf('%s%s:%s%s', $this->getColumnLetter($this->objSheetAtt['header_column_start']), $this->objSheetAtt['header_row_start'] + $this->objSheetAtt['header_row_count'], $this->getColumnLetter($this->objSheetAtt['header_column_start'] + $this->objSheetAtt['column_count'] - 1), $this->objSheetAtt['header_row_start'] + $this->objSheetAtt['header_row_count'] + $this->objSheetAtt['row_count'] - 1));

        //列宽，最小为10最大为30
        foreach ($this->arrColumnWidth as $column => $width) {
            $this->objSheet->getColumnDimension($column)->setWidth($width < 10 ? 10 : ($width > 30 ? 30 : $width));
        }

        //锁定标题行，如[0,2] 
        if ($this->objSheetAtt['sheet_option']['freeze']['freezeable']) {
            $this->objSheet->freezePaneByColumnAndRow($this->objSheetAtt['header_column_start'] + $this->objSheetAtt['sheet_option']['freeze']['column'], $this->objSheetAtt['header_row_start'] + $this->objSheetAtt['header_row_count'] + $this->objSheetAtt['sheet_option']['freeze']['row']);
        }
    }

    /**
     * 获取单元格在行列上的可合并单元格
     * @param int $strKey 当前表头内容
     * @param array $arrSource 所有表头数据
     * @param int $intCurRow 当前表头行
     * @param int $intCurColumn 当前表头列
     * @param array $arrExists 已处理的[行-列]组合
     * @param array $arrMerge 可合并的[行-列]组合
     */
    protected function checkMerge($strKey, $arrSource, $intCurRow, $intCurColumn, &$arrExists, &$arrMerge) {
        //1.向右查找，确定可合并列
        $intTmpColumn = $intCurColumn + 1;
        while ($intTmpColumn < count($arrSource[$intCurRow]) && $arrSource[$intCurRow][$intTmpColumn] == $strKey) {
            $intTmpColumn++;
        }
        $intRealColumn = $intTmpColumn - 1;
        //2.向下查找，确定可合并行
        $intTmpRow = $intCurRow + 1;
        $blnFlag = true;
        while ($blnFlag && $intTmpRow < count($arrSource)) {
            $blnTmpFlag = true;
            //需要行中所有列数据=$strKey
            for ($column = $intCurColumn; $column <= $intRealColumn; $column++) {
                if ($arrSource[$intTmpRow][$column] != $strKey) {
                    $blnTmpFlag = false;
                    break;
                }
            }
            if ($blnTmpFlag && $intTmpRow < count($arrSource)) {
                $intTmpRow++;
            } else {
                $blnFlag = false;
            }
        }
        $intRealRow = $intTmpRow - 1;
        //3.记录可合并项
        $arrMerge[] = $intCurColumn . '_' . $intCurRow . '_' . $intRealColumn . '_' . $intRealRow;
        //4.记录已处理单元格
        for ($row = $intCurRow; $row <= $intRealRow; $row++) {
            for ($column = $intCurColumn; $column <= $intRealColumn; $column++) {
                $arrExists[] = $row . '_' . $column;
            }
        }
    }

    /**
     * 处理表头数据
     * @param array $arrHeader
     */
    protected function dealHeader($arrHeaderOri, &$arrMerge) {
        //1.获取最大拆分次数
        $intLength = 0;
        foreach ($arrHeaderOri as $value) {
            $intLength = substr_count($value, '|') > $intLength ? substr_count($value, '|') : $intLength;
        }
        //2.格式化标题，补齐所有拆分
        foreach ($arrHeaderOri as $key => $value) {
            $intSplitCount = substr_count($value, '|');
            while ($intSplitCount < $intLength) {
                $arrCurValue = explode('|', $value);
                $arrHeaderOri[$key] = $arrHeaderOri[$key] . '|' . $arrCurValue[count($arrCurValue) - 1];
                $intSplitCount++;
            }
        }
        //3.将标题处理成二维数组
        $arrHeader = [];
        for ($row = 0; $row <= substr_count($arrHeaderOri[0], '|'); $row++) {
            $arrHeaderRow = [];
            foreach ($arrHeaderOri as $value) {
                $arrHeaderRow[] = explode('|', $value)[$row];
            }
            $arrHeader[] = $arrHeaderRow;
        }
        //4.找出可合并单元格，遍历单元格，然后向右(优先)向下查找可合并单元格
        $arrExists = [];
        for ($row = 0; $row < count($arrHeader); $row++) {
            for ($column = 0; $column < count($arrHeader[$row]); $column++) {
                if (!in_array($row . '_' . $column, $arrExists)) {
                    $key = $arrHeader[$row][$column];
                    $this->checkMerge($key, $arrHeader, $row, $column, $arrExists, $arrMerge);
                }
            }
        }
        return $arrHeader;
    }

    /**
     * 设置表头
     * @param array $arrData
     * @param array $arrColumnMap 
     */
    protected function createHeader($arrData, $arrColumnMap) {
        //1.获取表头数据
        $arrHeader = [];
        foreach ($arrData[0] as $key => $value) {
            if (isset($arrColumnMap[$key]) && $arrColumnMap[$key]['is_output'] == 1) {
                $strHeaderValue = $arrColumnMap[$key]['cname'];
            } else {
                $strHeaderValue = $key;
            }
            $arrHeader[] = $strHeaderValue;
        }
        $this->objSheetAtt['row_count'] = count($arrData);
        $this->objSheetAtt['column_count'] = count($arrHeader);
        //2.处理表头数据
        $arrMerge = [];
        $arrHeader = $this->dealHeader($arrHeader, $arrMerge);
        $this->objSheetAtt['header_row_count'] = count($arrHeader);
        //3.设置表头数据
        for ($intRow = 0; $intRow < count($arrHeader); $intRow++) {
            for ($intColumn = 0; $intColumn < count($arrHeader[0]); $intColumn++) {
                $objCell = $this->objSheet->getCellByColumnAndRow($this->objSheetAtt['header_column_start'] + $intColumn, $this->objSheetAtt['header_row_start'] + $intRow);
                $objCell->setValue($arrHeader[$intRow][$intColumn]);
                //记录列宽
                $intLetter = $this->getColumnLetter($this->objSheetAtt['header_column_start'] + $intColumn);
                $intLength = (strlen($arrHeader[$intRow][$intColumn]) + mb_strlen($arrHeader[$intRow][$intColumn], 'UTF8')) / 2;
                $this->arrColumnWidth[$intLetter] = (!isset($this->arrColumnWidth[$intLetter]) || $this->arrColumnWidth[$intLetter] < $intLength) ? $intLength : $this->arrColumnWidth[$intLetter];
            }
        }
        //4.合并表头
        foreach ($arrMerge as $value) {
            $arr = explode('_', $value);
            $this->objSheet->mergeCellsByColumnAndRow($this->objSheetAtt['header_column_start'] + $arr[0], $this->objSheetAtt['header_row_start'] + $arr[1], $this->objSheetAtt['header_column_start'] + $arr[2], $this->objSheetAtt['header_row_start'] + $arr[3]);
        }
    }

    /**
     * 写具体数据
     * @param array $arrData
     * @param array $arrColumnMap 
     */
    protected function createDetail($arrData, $arrColumnMap) {
        //是否需要写具体数据
        if (!$this->objSheetAtt['sheet_option']['body']['write_detail_data']) {
            $this->objSheetAtt['row_count'] = 0;
            return;
        }
        for ($i = 0; $i < count($arrData); $i++) {
            $intPRow = $this->objSheetAtt['header_row_start'] + $this->objSheetAtt['header_row_count'] + $i;
            $intPColumn = 0;
            foreach ($arrData[$i] as $key => $value) {
                $value = htmlspecialchars_decode($value, ENT_QUOTES);
                $arrColumnInfo = isset($arrColumnMap[$key]) ? $arrColumnMap[$key] : [];
                if (empty($arrColumnInfo) || (!empty($arrColumnInfo) && $arrColumnInfo['is_output'] == 1)) {
                    $objCell = $this->objSheet->getCellByColumnAndRow($this->objSheetAtt['header_column_start'] + $intPColumn, $intPRow);
                    $value = trim($value);
                    if (empty($arrColumnInfo) || (isset($arrColumnInfo['data_type']) && $arrColumnInfo['data_type'] == 1)) {
                        $objCell->setValueExplicit($value, PHPExcel_Cell_DataType::TYPE_STRING);
                    } else {
                        $objCell->setValue($value);
                    }
                    //记录列宽
                    $intLetter = $this->getColumnLetter($this->objSheetAtt['header_column_start'] + $intPColumn);
                    $intLength = ((strlen($value) + mb_strlen($value, 'UTF8')) / 2) + (strlen($value) == mb_strlen($value, 'UTF8') ? 3 : 0);
                    $this->arrColumnWidth[$intLetter] = (!isset($this->arrColumnWidth[$intLetter]) || $this->arrColumnWidth[$intLetter] < $intLength) ? $intLength : $this->arrColumnWidth[$intLetter];
                    $intPColumn++;
                }
            }
        }
    }

    /**
     * 生成excel文件
     * @param array $arrDatas 数据源集<br/>
     * $arrDatas=['员工信息'=>[['xingming'=>'张三','score'=>'98'],['xingming'=>'李四','score'=>'98']]]<br/>
     * 支持多行标题的生成，以【|】分隔。如['员工信息' => [['a' => '1', 'b|1|2|4' => '2', 'b|1|2|5' => '3', 'b|1|3' => '4', 'b|2' => '5', 'c' => '6', 'd|1' => '7', 'd|2' => '8', 'd|3' => '9', 'e|1|2' => '10']]]
     * @param array $arrColumnMaps 表头对应关系集。默认，表头为数据源列名，所有都导出，没有数据格式<br/>
     * $arrColumnMaps=['员工信息'=>['xingming'=>['cname'=>'姓名','data_type'=>'1','sql_type'=>'varchar','is_output'=>'1'],'score'=>['cname'=>'成绩','data_type'=>'2','sql_type'=>'float','is_output'=>'1']]];
     * @param bool $blnZip 是否压缩。默认，压缩
     * @param array $arrOptions sheet配置。可以配置sheet中表头与内容的填充色，字体色，边框色；锁定的行列，以实际内容区域为参考<br/>
     * $arrOptions=[
      '员工信息' => [
      'head' => ['background_color' => '#447131','font_color' => '#d04123','border_color' => '#d04123'],
      'body' => ['background_color' => '#bdbd36','font_color' => '#2323d6','border_color' => '#15aad4'],
      'freeze' => ['freezeable' => true,'row' => 0,'column' => 1]
      ]
      ];
     */
    public function createExcel($arrDatas = [], $arrColumnMaps = [], $blnZip = true, $arrOptions = []) {
        try {
            if (empty($arrDatas)) {
                return '';
            }
            //遍历数据集
            $intSheetIndex = 0;
            //删除原有的一个sheet
            $this->objExcel->removeSheetByIndex();
            foreach ($arrDatas as $strSheetName => $arrData) {
                if (empty($arrData) || !is_array($arrData)) {
                    continue;
                }
                //获取操作的sheet                    
                $this->objSheet = $this->objExcel->createSheet();
                $this->objSheet->setTitle($strSheetName);
                //1.获取sheet配置
                $this->setSheetOption(isset($arrOptions[$strSheetName]) ? $arrOptions[$strSheetName] : []);
                //2.设置表头
                $this->createHeader($arrData, isset($arrColumnMaps[$strSheetName]) ? $arrColumnMaps[$strSheetName] : []);
                //3.写具体数据
                $this->createDetail($arrData, isset($arrColumnMaps[$strSheetName]) ? $arrColumnMaps[$strSheetName] : []);
                //4.设置sheet样式
                $this->setSheetStyle();
                $intSheetIndex++;
            }
            if ($intSheetIndex == 0) {
                //没有有效sheet
                return '';
            }
            //4.保存excel文件
            $objWriter = PHPExcel_IOFactory::createWriter($this->objExcel, 'Excel2007');
            $strGuid = getGUID();
            $strAttPath = $this->getAttachPath();
            $strFileName = $strAttPath . $strGuid . '.xlsx';
            $objWriter->save($strFileName);
            //5.压缩文件
            if ($blnZip) {
                $strZipFileName = $strAttPath . $strGuid . '.zip';
                $strAttachName = $this->arrInit['cname'] . '.zip';
                $blnZipFlag = File::createZip(array(iconv('utf-8', 'gb2312', $this->arrInit['cname'] . '.xlsx') => $strFileName), $strZipFileName);
                if (!$blnZipFlag) {
                    $strZipFileName = $strFileName;
                    $strAttachName = $this->arrInit['cname'] . '.xlsx';
                }
            } else {
                $strZipFileName = $strFileName;
                $strAttachName = $this->arrInit['cname'] . '.xlsx';
            }
            //6.保存附件信息到数据库
            $this->arrInit['attach_id'] = $strGuid;
            $this->arrInit['path'] = $strZipFileName;
            $this->arrInit['cname'] = $strAttachName;
            File::saveAttach($this->arrInit);
            return $strGuid;
        } catch (Exception $e) {
            Log::log($e->getMessage(), Config::get('const.Log.LOG_ERR'));
            return '';
        }
    }

}
