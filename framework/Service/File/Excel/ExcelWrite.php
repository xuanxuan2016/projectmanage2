<?php

if (!defined('BM_COMMODULE_EXCEL_ClASS')) {
    define('BM_COMMODULE_EXCEL_ClASS', 1);

    include Lib_Path . 'PHPExcel/PHPExcel.php';

    /**
     * Excel操作类
     */
    class Excel {

        /**
         * 附件保存地址
         */
        private static $attachPath = Attach_Path;

        /**
         * Excel操作对象
         */
        private $objExcel;

        /**
         * Sheet操作对象
         */
        private $objSheet;

        /**
         * 生成Sheet的属性
         */
        private $objSheetAtt = array(
            "HeaderRowStart" => 1, //表头开始行
            "HeaderRowCount" => 1, //表头行数
            "HeaderColumnStart" => 0, //表头开始列    
            "RowCount" => 0, //实际数据行数
            "ColumnCount" => 0, //实际数据列数
            "SheetOption" => array()  //sheet属性
        );

        /**
         * 构造函数属性
         */
        private $arrInit = array();

        /**
         * 列宽
         */
        private $arrColumnWidth = array();

        /**
         * sheet属性
         */
        private $defaultOption = array(
            /**
             * 标题
             */
            "head" => array(
                /**
                 * 背景色，默认淡灰色
                 */
                "backgroundcolor" => "#d9d9d9",
                /**
                 * 字体色，默认黑色
                 */
                "fontcolor" => "#000000",
                /**
                 * 边框色，默认黑色
                 */
                "bordercolor" => "#000000",
                /**
                 * 标题是否加粗，默认是
                 */
                "bold" => true
            ),
            /**
             * 内容
             */
            "body" => array(
                /**
                 * 是否写具体数据，默认是
                 */
                "writedetaildata" => true,
                /**
                 * 背景色，默认白色
                 */
                "backgroundcolor" => "#FFFFFF",
                /**
                 * 字体色，默认黑色
                 */
                "fontcolor" => "#000000",
                /**
                 * 边框色，默认黑色
                 */
                "bordercolor" => "#000000"
            ),
            /**
             * 锁定
             */
            "freeze" => array(
                /**
                 * 是否需要锁定，默认需要锁定
                 */
                "freezeable" => true,
                /**
                 * 锁定行，默认为内容开始行
                 */
                "row" => 0,
                /**
                 * 锁定列，默认为内容开始列
                 */
                "column" => 0
            )
        );

        /**
         * Excel类
         * @param string $cname 附件名称，默认为导出文件
         * @param string $atttype 附件类型，默认为空；可以为，身份证，之类的标注
         * @param int $downdel 下载后是否删除文件，默认为是
         */
        public function __construct($cname = '导出文件', $atttype = '', $downdel = 1) {
            $this->objExcel = new PHPExcel();
            $this->arrInit['customerid'] = $this->getLoginInfo()->GetCurCustomerID();
            $this->arrInit['oprid'] = $this->getLoginInfo()->GetOprID();
            $this->arrInit['atttype'] = $atttype;
            $this->arrInit['cname'] = $cname;
            $this->arrInit['downdel'] = $downdel;
        }

        /**
         * 获取登录信息
         */
        private function getLoginInfo() {
            $loginInfo = Cookie::GetCookie('LoginInfo');
            if (!empty($loginInfo)) {
                $loginInfo = json_decode(urldecode($loginInfo), true);
                if (is_array($loginInfo)) {
                    $objLoginInfo = new LoginInfo();
                    $objLoginInfo->Map($loginInfo);
                    return $objLoginInfo;
                }
                return null;
            } else {
                return null;
            }
        }

        /**
         * 获取生成附件地址
         */
        private function getAttachPath() {
            $attpath = self::$attachPath;
            $attpath = $attpath . 'download/' . $this->arrInit['customerid'] . '/' . $this->arrInit['oprid'] . '/' . date('Ymd') . '/';
            if (!is_dir($attpath)) {
                mkdir($attpath, 0777, true);
            }
            return $attpath;
        }

        /**
         * 获取sheet的样式-表头
         */
        private function getSheetStyleHeader() {
            $sheetStyle = new PHPExcel_Style();
            $sheetStyle->applyFromArray(
                    array('fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('argb' => str_replace('#', 'FF', $this->objSheetAtt['SheetOption']['head']['backgroundcolor']))
                        ),
                        'borders' => array(
                            'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, "color" => array('argb' => str_replace('#', 'FF', $this->objSheetAtt['SheetOption']['head']['bordercolor']))),
                            'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, "color" => array('argb' => str_replace('#', 'FF', $this->objSheetAtt['SheetOption']['head']['bordercolor']))),
                            'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, "color" => array('argb' => str_replace('#', 'FF', $this->objSheetAtt['SheetOption']['head']['bordercolor']))),
                            'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN, "color" => array('argb' => str_replace('#', 'FF', $this->objSheetAtt['SheetOption']['head']['bordercolor'])))
                        ),
                        'font' => array(
                            'name' => 'Arial',
                            'size' => '9',
                            'bold' => $this->objSheetAtt['SheetOption']['head']['bold'],
                            'color' => array('argb' => str_replace('#', 'FF', $this->objSheetAtt['SheetOption']['head']['fontcolor']))
                        ),
                        'numberformat' => array(
                            'code' => PHPExcel_Style_NumberFormat::FORMAT_TEXT
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                            'shrinkToFit' => true
                        )
            ));
            return $sheetStyle;
        }

        /**
         * 获取sheet的样式-具体数据
         */
        private function getSheetStyleDetail() {
            $sheetStyle = new PHPExcel_Style();
            $sheetStyle->applyFromArray(
                    array('fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('argb' => str_replace('#', 'FF', $this->objSheetAtt['SheetOption']['body']['backgroundcolor']))
                        ),
                        'borders' => array(
                            'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, "color" => array('argb' => str_replace('#', 'FF', $this->objSheetAtt['SheetOption']['body']['bordercolor']))),
                            'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, "color" => array('argb' => str_replace('#', 'FF', $this->objSheetAtt['SheetOption']['body']['bordercolor']))),
                            'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, "color" => array('argb' => str_replace('#', 'FF', $this->objSheetAtt['SheetOption']['body']['bordercolor']))),
                            'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN, "color" => array('argb' => str_replace('#', 'FF', $this->objSheetAtt['SheetOption']['body']['bordercolor'])))
                        ),
                        'font' => array(
                            'name' => 'Arial',
                            'size' => '9',
                            'color' => array('argb' => str_replace('#', 'FF', $this->objSheetAtt['SheetOption']['body']['fontcolor']))
                        ),
                        'numberformat' => array(
                            'code' => PHPExcel_Style_NumberFormat::FORMAT_TEXT
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                        )
            ));
            return $sheetStyle;
        }

        /**
         * 设置sheet属性
         * @param type $option
         */
        private function setSheetOption($option) {
            foreach ($this->defaultOption as $key => $value) {
                if (isset($option[$key])) {
                    $this->objSheetAtt['SheetOption'][$key] = array_merge($value, $option[$key]);
                } else {
                    $this->objSheetAtt['SheetOption'][$key] = $value;
                }
            }
        }

        /**
         * 根据列数获取excel列名
         * @param type $pcolumn 列数
         */
        private function getColumnLetter($pcolumn) {
            return PHPExcel_Cell::stringFromColumnIndex($pcolumn);
        }

        /**
         * 设置sheet样式
         */
        private function setSheetStyle() {
            //单元格样式
            //标题行：如A1:AI1
            $this->objSheet->setSharedStyle($this->getSheetStyleHeader(), sprintf("%s%s:%s%s", $this->getColumnLetter($this->objSheetAtt['HeaderColumnStart']), $this->objSheetAtt['HeaderRowStart'], $this->getColumnLetter($this->objSheetAtt['HeaderColumnStart'] + $this->objSheetAtt['ColumnCount'] - 1), $this->objSheetAtt['HeaderRowStart'] + $this->objSheetAtt['HeaderRowCount'] - 1));
            //数据行：如A2:AI150
            $this->objSheet->setSharedStyle($this->getSheetStyleDetail(), sprintf("%s%s:%s%s", $this->getColumnLetter($this->objSheetAtt['HeaderColumnStart']), $this->objSheetAtt['HeaderRowStart'] + $this->objSheetAtt['HeaderRowCount'], $this->getColumnLetter($this->objSheetAtt['HeaderColumnStart'] + $this->objSheetAtt['ColumnCount'] - 1), $this->objSheetAtt['HeaderRowStart'] + $this->objSheetAtt['HeaderRowCount'] + $this->objSheetAtt['RowCount'] - 1));

            //列宽，最小为10最大为30
            foreach ($this->arrColumnWidth as $column => $width) {
                $this->objSheet->getColumnDimension($column)->setWidth($width < 10 ? 10 : ($width > 30 ? 30 : $width));
            }

            //锁定标题行，如[0,2] 
            if ($this->objSheetAtt['SheetOption']['freeze']['freezeable']) {
                $this->objSheet->freezePaneByColumnAndRow($this->objSheetAtt['HeaderColumnStart'] + $this->objSheetAtt['SheetOption']['freeze']['column'], $this->objSheetAtt['HeaderRowStart'] + $this->objSheetAtt['HeaderRowCount'] + $this->objSheetAtt['SheetOption']['freeze']['row']);
            }
        }

        /**
         * 获取单元格在行列上的可合并单元格
         * @param type $key
         * @param type $source
         * @param type $currow
         * @param type $curcolumn
         * @param string $arrExists
         * @param array $arrMerge
         */
        private function checkmerge($key, $source, $currow, $curcolumn, &$arrExists, &$arrMerge) {
            //1.向右查找，确定可合并列
            $tmpcolumn = $curcolumn + 1;
            while ($source[$currow][$tmpcolumn] == $key && $tmpcolumn < count($source[$currow])) {
                $tmpcolumn++;
            }
            $realcolumn = $tmpcolumn - 1;
            //2.向下查找，确定可合并行
            $tmprow = $currow + 1;
            $flag = true;
            while ($flag) {
                $tmpflag = true;
                //需要行中所有列数据=$key
                for ($column = $curcolumn; $column <= $realcolumn; $column++) {
                    if ($source[$tmprow][$column] != $key) {
                        $tmpflag = false;
                        break;
                    }
                }
                if ($tmpflag && $tmprow < count($source)) {
                    $tmprow++;
                } else {
                    $flag = false;
                }
            }
            $realrow = $tmprow - 1;
            //3.记录可合并项
            $arrMerge[] = $curcolumn . '_' . $currow . '_' . $realcolumn . '_' . $realrow;
            //4.记录已处理单元格
            for ($row = $currow; $row <= $realrow; $row++) {
                for ($column = $curcolumn; $column <= $realcolumn; $column++) {
                    $arrExists[] = $row . '_' . $column;
                }
            }
        }

        /**
         * 处理表头数据
         * @param type $header
         */
        protected function dealHeader($header, &$arrMerge) {
            //1.获取最大拆分次数
            foreach ($header as $value) {
                $length = substr_count($value, '|') > $length ? substr_count($value, '|') : $length;
            }
            //2.格式化标题，补齐所有拆分
            foreach ($header as $key => $value) {
                $splitcount = substr_count($value, '|');
                while ($splitcount < $length) {
                    $curvalue = explode('|', $value);
                    $header[$key] = $header[$key] . '|' . $curvalue[count($curvalue) - 1];
                    $splitcount++;
                }
            }
            //3.将标题处理成二维数组
            $arrHeader = array();
            for ($row = 0; $row <= substr_count($header[0], '|'); $row++) {
                $arrHeaderRow = array();
                foreach ($header as $value) {
                    $arrHeaderRow[] = explode('|', $value)[$row];
                }
                $arrHeader[] = $arrHeaderRow;
            }
            //4.找出可合并单元格，遍历单元格，然后向右(优先)向下查找可合并单元格
            $arrExists = array();
            for ($row = 0; $row < count($arrHeader); $row++) {
                for ($column = 0; $column < count($arrHeader[$row]); $column++) {
                    if (!in_array($row . '_' . $column, $arrExists)) {
                        $key = $arrHeader[$row][$column];
                        $this->checkmerge($key, $arrHeader, $row, $column, $arrExists, $arrMerge);
                    }
                }
            }
            return $arrHeader;
        }

        /**
         * 设置表头
         * @param type $data
         * @param type $columnmap 
         */
        protected function createHeader($data, $columnmap) {
            //1.获取表头数据
            $header = array();
            foreach ($data[0] as $key => $value) {
                $column = $columnmap[$key];
                if (empty($column) || (!empty($column) && $column['isoutput'] == 1)) {
                    $headervalue = trim((!empty($column) ? $column['cname'] : $key));
                    $header[] = $headervalue;
                }
            }
            $this->objSheetAtt['RowCount'] = count($data);
            $this->objSheetAtt['ColumnCount'] = count($header);
            //2.处理表头数据
            $arrMerge = array();
            $header = $this->dealHeader($header, $arrMerge);
            $this->objSheetAtt['HeaderRowCount'] = count($header);
            //3.设置表头数据
            for ($row = 0; $row < count($header); $row++) {
                for ($column = 0; $column < count($header[0]); $column++) {
                    $cell = $this->objSheet->getCellByColumnAndRow($this->objSheetAtt['HeaderColumnStart'] + $column, $this->objSheetAtt['HeaderRowStart'] + $row);
                    $cell->setValue($header[$row][$column]);
                    //记录列宽
                    $letter = $this->getColumnLetter($this->objSheetAtt['HeaderColumnStart'] + $column);
                    $length = (strlen($header[$row][$column]) + mb_strlen($header[$row][$column], 'UTF8')) / 2;
                    $this->arrColumnWidth[$letter] = (!isset($this->arrColumnWidth[$letter]) || $this->arrColumnWidth[$letter] < $length) ? $length : $this->arrColumnWidth[$letter];
                }
            }
            //4.合并表头
            foreach ($arrMerge as $value) {
                $arr = explode('_', $value);
                $this->objSheet->mergeCellsByColumnAndRow($this->objSheetAtt['HeaderColumnStart'] + $arr[0], $this->objSheetAtt['HeaderRowStart'] + $arr[1], $this->objSheetAtt['HeaderColumnStart'] + $arr[2], $this->objSheetAtt['HeaderRowStart'] + $arr[3]);
            }
        }

        /**
         * 写具体数据
         * @param type $data
         * @param type $columnmap 
         */
        protected function createDetail($data, $columnmap) {
            //是否需要写具体数据
            if (!$this->objSheetAtt['SheetOption']['body']['writedetaildata']) {
                $this->objSheetAtt['RowCount'] = 0;
                return;
            }
            for ($i = 0; $i < count($data); $i++) {
                $pRow = $this->objSheetAtt['HeaderRowStart'] + $this->objSheetAtt['HeaderRowCount'] + $i;
                $pColumn = 0;
                foreach ($data[$i] as $key => $value) {
                    $value = htmlspecialchars_decode($value, ENT_QUOTES);
                    $column = $columnmap[$key];
                    if (empty($column) || (!empty($column) && $column['isoutput'] == 1)) {
                        $cell = $this->objSheet->getCellByColumnAndRow($this->objSheetAtt['HeaderColumnStart'] + $pColumn, $pRow);
                        $value = trim($value);
                        if (empty($column) || $column['datatype'] == 1) {
                            $cell->setValueExplicit($value, PHPExcel_Cell_DataType::TYPE_STRING);
                        } else {
                            $cell->setValue($value);
                        }
                        //记录列宽
                        $letter = $this->getColumnLetter($this->objSheetAtt['HeaderColumnStart'] + $pColumn);
                        $length = ((strlen($value) + mb_strlen($value, 'UTF8')) / 2) + (strlen($value) == mb_strlen($value, 'UTF8') ? 3 : 0);
                        $this->arrColumnWidth[$letter] = (!isset($this->arrColumnWidth[$letter]) || $this->arrColumnWidth[$letter] < $length) ? $length : $this->arrColumnWidth[$letter];
                        $pColumn++;
                    }
                }
            }
        }

        /**
         * 创建压缩文件
         * @param type $sourcefile 需要压缩的文件数组，key为文件在压缩包里的文件名，value为被压缩的文件路径。array("导出文件.xlsx"=>"\www\导出文件.xlsx")
         * @param type $destination 压缩到的位置
         */
        public function createZip($sourcefile = array(), $destination = '') {
            //得到有效的文件路径
            $valid_files = array();
            if (is_array($sourcefile)) {
                foreach ($sourcefile as $zipfilename => $filename) {
                    if (file_exists($filename)) {
                        $valid_files[$zipfilename] = $filename;
                    }
                }
            }
            //将有效文件添加到压缩文件中
            if (count($valid_files)) {
                $zip = new ZipArchive();
                if ($zip->open($destination, file_exists($destination) ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
                    return false;
                }
                //添加文件
                foreach ($valid_files as $zipfilename => $filename) {
                    $zip->addFile($filename, $zipfilename);
                }
                $zip->close();
                //检查是否压缩成功
                $zipSuccess = file_exists($destination);
                //压缩成功删除文件
                if ($zipSuccess) {
                    foreach ($valid_files as $filename) {
                        unlink($filename);
                    }
                }
                return $zipSuccess;
            } else {
                return false;
            }
        }

        /**
         * 生成excel文件
         * @param array $datas 数据源集<br/>
         * $datas=array('员工信息'=>array(array("xingming"=>"张三","score"=>"98"),array("xingming"=>"李四","score"=>"98")))<br/>
         * 支持多行标题的生成，以【|】分隔。如array('员工信息' => array(array("a" => "1", "b|1|2|4" => "2", "b|1|2|5" => "3", "b|1|3" => "4", "b|2" => "5", "c" => "6", "d|1" => "7", "d|2" => "8", "d|3" => "9", "e|1|2" => "10")))
         * @param array $columnmaps 表头对应关系集。默认，表头为数据源列名，所有都导出，没有数据格式<br/>
         * $columnmaps=array('员工信息'=>array("xingming"=>array("cname"=>"姓名","datatype"=>"1","sqltype"=>"varchar","isoutput"=>"1"),"score"=>array("cname"=>"成绩","datatype"=>"2","sqltype"=>"float","isoutput"=>"1")))
         * @param bool $iszip 是否压缩。默认，压缩
         * @param array $options sheet配置。可以配置sheet中表头与内容的填充色，字体色，边框色；锁定的行列，以实际内容区域为参考<br/>
         * $options=array(
          '员工信息' => array(
          "head" => array("backgroundcolor" => "#447131","fontcolor" => "#d04123","bordercolor" => "#d04123"),
          "body" => array("backgroundcolor" => "#bdbd36","fontcolor" => "#2323d6","bordercolor" => "#15aad4"),
          "freeze" => array("freezeable" => true,"row" => 0,"column" => 1)
          )
          );
         */
        public function createExcel($datas = array(), $columnmaps = array(), $iszip = true, $options = array()) {
            try {
                if (empty($datas)) {
                    return '';
                }
                //设置脚本超时时间为300秒
                set_time_limit(300);
                //遍历数据集
                $sheetindex = 0;
                //删除原有的一个sheet
                $this->objExcel->removeSheetByIndex();
                foreach ($datas as $sheetname => $data) {
                    if (empty($data) || !is_array($data)) {
                        continue;
                    }
                    //获取操作的sheet                    
                    $this->objSheet = $this->objExcel->createSheet();
                    $this->objSheet->setTitle($sheetname);
                    //1.获取sheet配置
                    $this->setSheetOption($options[$sheetname]);
                    //2.设置表头
                    $this->createHeader($data, $columnmaps[$sheetname]);
                    //3.写具体数据
                    $this->createDetail($data, $columnmaps[$sheetname]);
                    //4.设置sheet样式
                    $this->setSheetStyle();
                    $sheetindex++;
                }
                if ($sheetindex == 0) {
                    //没有有效sheet
                    return '';
                }
                //4.保存excel文件
                $objWriter = PHPExcel_IOFactory::createWriter($this->objExcel, 'Excel2007');
                $guid = Func::getGUID();
                $attpath = $this->getAttachPath();
                $filename = $attpath . $guid . '.xlsx';
                $objWriter->save($filename);
                //5.压缩文件
                if ($iszip) {
                    $zipfilename = $attpath . $guid . '.zip';
                    $cname = $this->arrInit['cname'] . '.zip';
                    $zipflag = $this->createZip(array(iconv('utf-8', 'gb2312', $this->arrInit['cname'] . ".xlsx") => $filename), $zipfilename);
                    if (!$zipflag) {
                        $zipfilename = $filename;
                        $cname = $this->arrInit['cname'] . '.xlsx';
                    }
                } else {
                    $zipfilename = $filename;
                    $cname = $this->arrInit['cname'] . '.xlsx';
                }
                //6.保存附件信息到数据库
                include BLL_Path . 'common/Attach.BLL.php';
                $this->arrInit['attid'] = $guid;
                $this->arrInit['attpath'] = $zipfilename;
                $this->arrInit['cname'] = $cname;
                $objBLLAttach = new BLLAttach();
                $objBLLAttach->saveAttach($this->arrInit);
                return $guid;
            } catch (Exception $e) {
                Log::getInstance()->log($e->getMessage(), LOG::LOG_ERR);
                return '';
            }
        }

    }

}
?>