<?php

namespace Framework\Service\File\Excel;

/**
 * 这里需要use，否则会认为PHPExcel_IOFactory为Framework\Service\File\Excel命名空间下的类
 */
use PHPExcel_IOFactory;
use Framework\Facade\App;

/**
 * ExcelRead类
 */
class ExcelRead {

    /**
     * 文件路径
     */
    protected $strFilePath = '';

    /**
     * 文件类型
     */
    protected $strFileType = '';

    /**
     * 构造函数
     */
    public function __construct() {
        //加载phpexcel
        include_once App::make('path.framework') . '/Service/Lib/PHPExcel/PHPExcel/IOFactory.php';
    }

    /**
     * 初始化类
     * @param string $strFilePath excel路径
     */
    public function init($strFilePath = '') {
        try {
            if (file_exists($strFilePath)) {
                $this->strFilePath = $strFilePath;
                $this->strFileType = PHPExcel_IOFactory::identify($strFilePath);
            }
        } catch (Exception $e) {
            $this->strFilePath = '';
            $this->strFileType = '';
        } finally {
            return $this;
        }
    }

    /**
     * 检查文件是否存在且格式正确
     */
    protected function checkFile() {
        if (!empty($this->strFileType) && in_array($this->strFileType, ['Excel5', 'Excel2007'])) {
            return true;
        }
        return false;
    }

    /**
     * 检查sheet信息是否正确
     * @param mix $mixValue sheetindex或sheetname
     * @return string sheetname
     */
    protected function checkSheetName($mixValue) {
        //输入参数是否为index
        $isSheetIndex = gettype($mixValue) === 'integer' ? true : false;
        foreach ($this->getSheets() as $arrSheetInfo) {
            if ($isSheetIndex) {
                if ($arrSheetInfo['sheetindex'] === $mixValue) {
                    return $arrSheetInfo['sheetname'];
                }
            } else {
                if ($arrSheetInfo['sheetname'] === $mixValue) {
                    return $arrSheetInfo['sheetname'];
                }
            }
        }
        return '';
    }

    /**
     * 根据sheetname获取数据
     * @param string $strSheetName sheetname
     * @return array sheet中的值
     */
    protected function getData($strSheetName) {
        try {
            $objReader = PHPExcel_IOFactory::createReader($this->strFileType);
            $objReader->setLoadSheetsOnly($strSheetName);
            $objExcel = $objReader->load($this->strFilePath);
            return $objExcel->getActiveSheet()->toArray('', false, false, true);
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * 格式化获取到的数据
     * @param array $arrSheetData 需格式化的数据
     * @param int $intRowCount 需要读取的数据行数
     * @param int $intHeaderCount sheet标题行数
     * @param int $intHeaderStart sheet标题开始行
     */
    protected function formateData($arrSheetData, $intRowCount, $intHeaderCount, $intHeaderStart) {
        $intStartRow = 1;
        //1.获取数据开始行
        if ($intHeaderStart === -1) {
            //定位数据开始行，以某一行数据有一个单元格不为空为准
            foreach ($arrSheetData as $intRowIndex => $arrDataRow) {
                $blnTmpFlag = false;
                foreach ($arrDataRow as $strDataCell) {
                    if ($strDataCell !== '') {
                        $blnTmpFlag = true;
                    }
                }
                if ($blnTmpFlag) {
                    $intStartRow = $intRowIndex;
                    break;
                }
            }
        } else {
            $intStartRow = $intHeaderStart;
        }
        //2.如果数据行数小于标题行数
        if (count($arrSheetData) < $intStartRow + $intHeaderCount - 1) {
            return ["err_msg" => 'sheet中数据行数小于标题行数'];
        }
        //3.获取标题行名称，如果标题行多行的则拼接标题行(以|分隔)
        $arrHeaderColumnTmp = [];
        for ($intRow = $intStartRow; $intRow < $intStartRow + $intHeaderCount; $intRow++) {
            $strFrontKey = '';
            foreach ($arrSheetData[$intRow] as $key => $value) {
                //单元格数据为空，
                if ($value === '') {
                    //1.检查下面行对应列是否都为空
                    $intTmpRow = $intRow + 1;
                    $blnTmpFlag = false;
                    while ($intTmpRow < $intStartRow + $intHeaderCount) {
                        if ($arrSheetData[$intTmpRow][$key] !== '') {
                            $blnTmpFlag = true;
                            break;
                        }
                        $intTmpRow++;
                    }
                    //2.如果下面行对应列有值且列上面行对应列有值取上面行对应列值，否则取当前行左边的数据
                    if ($blnTmpFlag) {
                        //上面行对应列是否不为空
                        $intTmpRow = $intRow - 1;
                        $blnTmpFlag = false;
                        while ($intTmpRow >= $intStartRow) {
                            if ($arrSheetData[$intTmpRow][$key] !== '') {
                                $blnTmpFlag = true;
                                break;
                            }
                            $intTmpRow--;
                        }
                        //上面行对应列有值取值，否则用当前行左边列的值
                        if ($blnTmpFlag) {
                            //$value = $arrSheetData[$intTmpRow][$key];
                            $value = '';
                        } else {
                            $value = $arrHeaderColumnTmp2[$strFrontKey];
                        }
                    }
                }
                $strFrontKey = $key;
                $arrHeaderColumnTmp2[$key] = $value;
            }
            $arrHeaderColumnTmp[] = $arrHeaderColumnTmp2;
        }
        foreach ($arrHeaderColumnTmp as $header) {
            foreach ($header as $key => $value) {
                $arrHeaderColumn[$key] = isset($arrHeaderColumn[$key]) ? ($arrHeaderColumn[$key] . ($value !== '' ? '|' : '') . $value) : $value;
            }
        }
        foreach ($arrHeaderColumn as $key => $value) {
            //剔除标题行为空的
            if ($arrHeaderColumn[$key] === '') {
                unset($arrHeaderColumn[$key]);
            }
        }
        //重复表头
        $repeatColumn = array_diff_assoc($arrHeaderColumn, array_unique($arrHeaderColumn, SORT_STRING));
        if (!empty($repeatColumn)) {
            return ["err_msg" => '表头重复，重复表头为【' . implode(',', $repeatColumn) . '】'];
        }
        //4.根据需要读取的数据组成返回数据
        $intDataStartRow = $intStartRow + $intHeaderCount; //数据开始行
        $arrFormateData = [];
        $intCurrentDatarow = $intDataStartRow; //当前读取的行数
        $intReadFlag = 1; //已经读取的行数
        while (($intReadFlag <= $intRowCount || $intRowCount == -1) && !empty($arrSheetData[$intCurrentDatarow])) {
            $blnTmpFlag = false;
            $arrTmpdata = [];
            $arrRowData = $arrSheetData[$intCurrentDatarow]; //sheet中某行数据
            foreach ($arrHeaderColumn as $sheetcol => $realcol) {
                if ($arrRowData[$sheetcol] !== '') {
                    $blnTmpFlag = true;
                }
                $arrTmpdata[$realcol] = $arrRowData[$sheetcol];
            }
            //行中数据不是全空时才需要
            if ($blnTmpFlag) {
                $intReadFlag++;
                $arrFormateData[] = $arrTmpdata;
            }
            $intCurrentDatarow++;
        }
        return $arrFormateData;
    }

    /**
     * 获取excel中所有sheet信息
     * @return array sheet信息，如[['sheetindex'=>'0','sheetname'=>'a'],['sheetindex'=>'1','sheetname'=>'b']]
     */
    public function getSheets() {
        try {
            if (!$this->checkFile()) {
                return [];
            }
            $objReader = PHPExcel_IOFactory::createReader($this->strFileType);
            $arrSheetNames = $objReader->listWorksheetNames($this->strFilePath);
            $arrSheets = [];
            foreach ($arrSheetNames as $index => $name) {
                $arrSheets[] = ['sheetindex' => $index, 'sheetname' => $name];
            }
            return $arrSheets;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 获取excel中某个sheet的数据
     * @param mix $mixSheet sheetindex或者sheetname
     * <br/>sheetindex从0开始
     * @param int $intRowCount 需要读取的数据行数，默认(-1)全部读取
     * @param int $intHeaderCount sheet标题行数，默认1行
     * @param int $intHeaderStart sheet标题开始行，默认(-1)自动计算【以某一行数据有一个单元格不为空为准】
     * <br/>可手动设置标题行为sheet中第几行
     * @return array 如果成功，返回sheet数据
     * <br/>如果失败，返回为["err_msg"=>"错误信息"]
     */
    public function getSheetData($mixSheet, $intRowCount = -1, $intHeaderCount = 1, $intHeaderStart = -1) {
        try {
            //1.检查sheet信息
            $strSheetName = $this->checkSheetName($mixSheet);
            if (empty($strSheetName)) {
                return ["err_msg" => '未找到指定sheet'];
            }
            //2.获取sheet原数据
            $arrSheetData = $this->getData($strSheetName);
            if (empty($arrSheetData)) {
                return ["err_msg" => 'sheet中数据为空'];
            }
            //3.根据标题行数及需求数据行数，获取格式化的数据
            $arrSheetData = $this->formateData($arrSheetData, $intRowCount, $intHeaderCount, $intHeaderStart);
            return $arrSheetData;
        } catch (Exception $e) {
            return [];
        }
    }

}
