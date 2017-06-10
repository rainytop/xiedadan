<?php

namespace Vendor\Hiland\Utils\Office;
/**
 * Created by PhpStorm.
 * User: xiedalie
 * Date: 2017/6/10
 * Time: 9:51
 */

require_once 'autoload.php';

class ExcelHelper
{
    public static function getSheetContent($excelFile, $sheetIndex = 0, $titleRowNumber = 1)
    {
        if ($titleRowNumber < 0) {
            $titleRowNumber = 0;
        }

        $objPHPExcel = \PHPExcel_IOFactory::load($excelFile);


        $sheet = $objPHPExcel->getSheet($sheetIndex);

        //获取行数与列数,注意列数需要转换
        $highestRowNum = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnNum = \PHPExcel_Cell::columnIndexFromString($highestColumn);

        //取得字段，这里测试表格中的第一行为数据的字段，因此先取出用来作后面数组的键名
        $filed = array();

        for ($i = 0; $i < $highestColumnNum; $i++) {
            if ($titleRowNumber == 0) {
                $cellVal = $i;
            } else {
                $cellName = \PHPExcel_Cell::stringFromColumnIndex($i) . $titleRowNumber;
                $cellVal = $sheet->getCell($cellName)->getValue();//取得列内容
            }

            $filed [] = $cellVal;
        }


        //开始取出数据并存入数组
        $data = array();
        for ($i = $titleRowNumber+1;
             $i <= $highestRowNum;
             $i++) {//ignore row title
            $row = array();
            for ($j = 0;
                 $j < $highestColumnNum;
                 $j++) {
                $cellName = \PHPExcel_Cell::stringFromColumnIndex($j) . $i;
                $cellVal = $sheet->getCell($cellName)->getValue();
                $row[$filed[$j]] = $cellVal;
            }

            $data [] = $row;
        }

        return $data;
    }
}