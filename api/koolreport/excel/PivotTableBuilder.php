<?php

namespace koolreport\excel;

use \koolreport\core\Utility as Util;
use \PhpOffice\PhpSpreadsheet as ps;
use \PhpOffice\PhpSpreadsheet\Style\Alignment;
use \PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use \Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use \Box\Spout\Common\Entity\Style\CellAlignment;

class PivotTableBuilder extends WidgetBuilder
{
    protected $template = "pivottable";

    public function saveContentToSheet($content, $sheet)
    {
        $this->sheet = $sheet;

        list($highestRow, $highestColumn, $range) =
            $this->getSheetRange($sheet, $content);
        $option = $content;
        $pos = Coordinate::coordinateFromString($range[1]);
        $option['startColumn'] = Coordinate::columnIndexFromString($pos[0]);
        $option['startRow'] = $pos[1];

        $this->option = $option;
        $this->saveDataStoreToExcel();
    }

    public function saveContentToBigSpreadsheet($content, $writer)
    {
        $this->writer = $writer;

        $translation = Util::get($content, ['attributes', 'translation'], "0:0");
        $translation = explode(":", $translation);
        $colTrans = $translation[0];
        $rowTrans = $translation[1];
        for ($i = 0; $i < $rowTrans; $i++) {
            $emptyRow = WriterEntityFactory::createRowFromArray([]);
            $writer->addRow($emptyRow);
        }
        $content['startColumn'] = $colTrans;
        $this->option = $content;
        $this->saveDataStoreToBigSpreadsheet();
    }

    protected function buildDatastore()
    {
        $option = $this->option;
        $ds = Util::get($option, 'dataSource', new \koolreport\core\DataStore());
        $filtering = Util::get($option, 'filtering', null);
        if (!empty($filtering)) {
            $ds = $ds->filter($filtering);
        }
        $sorting = Util::get($option, 'sorting', null);
        if (!empty($sorting)) {
            $ds = $ds->sort($sorting);
        }
        $paging = Util::get($option, 'paging', null);
        if (!empty($paging)) {
            $ds = $ds->paging($paging[0], $paging[1]);
        }
        return $ds;
    }

    protected function simulateMergeCells($cell, $endCell)
    {
        if ($this->exportType === "excel") {
            $style = $this->sheet->getStyle($cell . ":" . $endCell);
            $excelStyle = [
                'borders' => [
                    'outline' => [
                        'borderStyle' => 'thin', //dashDot, dashDotDot, dashed, dotted, double, hair, medium, mediumDashDot, mediumDashDotDot, mediumDashed, slantDashDot, thick, thin
                        'color' => [
                            'rgb' => 'BBBBBB',
                        ]
                    ],
                    //left, right, bottom, diagonal, allBorders, outline, inside, vertical, horizontal
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'color' => [
                        'rgb' => 'FFFFFF',
                    ],
                ]
            ];
            $style->applyFromArray($excelStyle);
        }
    }

    protected function rowstoExcel($rowsInfo)
    {
        $sheet = $this->sheet;
        foreach ($this->rowsInfo as $x => $rowInfo) {
            foreach ($rowInfo as $y => $cellInfo) {
                $cellAddress = Coordinate::stringFromColumnIndex($y) . $x;
                $cellValue = Util::get($cellInfo, "cellValue");
                // echo "cellAddress=$cellAddress<br>"; 
                // echo "cellValue=$cellValue<br>"; 
                $sheet->setCellValue($cellAddress, $cellValue);
                $styleArray = Util::get($cellInfo, 'styleArray');
                $style = $this->sheet->getStyle($cellAddress);
                if (is_array($styleArray)) {
                    $style->applyFromArray($styleArray);
                }
                $formatCode = Util::get($styleArray, 'formatCode');
                if (!empty($formatCode)) {
                    $style->getNumberFormat()->setFormatCode($formatCode);
                }
            }
        }
    }

    protected function rowstoBigSpreadsheet($rowsInfo)
    {
        ksort($rowsInfo);
        // Util::prettyPrint($this->rowsInfo);
        foreach ($rowsInfo as $x => $rowInfo) {
            $rowObj = [];
            ksort($rowInfo);
            // Util::prettyPrint($rowInfo);
            $yCounter = 0;
            foreach ($rowInfo as $y => $cellInfo) {
                if ($yCounter === 0) {
                    for ($i = 0; $i < $y; $i++) $rowObj[] = WriterEntityFactory::createCell(null, null);
                }
                $yCounter++;
                $cellValue = Util::get($cellInfo, "cellValue");
                $styleArray = Util::get($cellInfo, 'styleArray');
                $cellObj = WriterEntityFactory::createCell(
                    $cellValue,
                    $this->getSpreadsheetStyleObj($styleArray)
                );
                $rowObj[] = $cellObj;
            }
            $rowFromValues = WriterEntityFactory::createRow($rowObj);
            $this->writer->addRow($rowFromValues);
        }
        // exit;
    }

    protected function toRowsInfo($args)
    {
        $row = Util::get($args, "row");
        $col = Util::get($args, "col");
        $endRow = Util::get($args, "endRow", $row);
        $endCol = Util::get($args, "endCol", $col);
        $cellValue = Util::get($args, "cellValue");
        $styleArray = Util::get($args, "styleArray", []);
        $duplicateValue = Util::get($args, "duplicateValue", false);
        $hAlignmentExcel = Util::get($args, "hAlignmentExcel", Alignment::HORIZONTAL_CENTER);
        $vAlignmentExcel = Util::get($args, "vAlignmentExcel", Alignment::VERTICAL_CENTER);
        $hAlignmentSpreadsheet = Util::get($args, "hAlignmentSpreadsheet", CellAlignment::CENTER);
        $vAlignmentSpreadsheet = Util::get($args, "vAlignmentSpreadsheet", CellAlignment::CENTER);
        if ($this->exportType === "excel") {
            if ($col != $endCol || $row != $endRow) {
                $cell = Coordinate::stringFromColumnIndex($col) . $row;
                $endCell = Coordinate::stringFromColumnIndex($endCol) . $endRow;
                if ($this->mergeCells) $this->sheet->mergeCells($cell . ":" . $endCell);
                else if (!$duplicateValue) $this->simulateMergeCells($cell, $endCell);
            }
        }

        for ($x = $row; $x <= $endRow; $x++) {
            for ($y = $col; $y <= $endCol; $y++) {
                $cellInfo = Util::init($this->rowsInfo, [$x, $y], []);
                $cellInfo["cellValue"] = $cellValue;
                if (!$duplicateValue) $cellValue = null;
                $hAlignment = $this->exportType === 'excel' ? $hAlignmentExcel : $hAlignmentSpreadsheet;
                $vAlignment = $this->exportType === 'excel' ? $vAlignmentExcel : $vAlignmentSpreadsheet;
                Util::set($styleArray, ['alignment', 'horizontal'], $hAlignment);
                Util::set($styleArray, ['alignment', 'vertical'], $vAlignment);
                if ($this->exportType === "bigspreadsheet" && !$duplicateValue) {
                    $styleArray['backgroundColor'] = 'FFFFFF';
                    $thinNoneBorder = [ 'style' => 'none', 'width' => 'thin', 'color' => 'AAAAAA'];
                    $border = [
                        // 'color' => 'AAAAAA',
                        // 'width' => 'thin',
                        'top' => $thinNoneBorder,
                        'right' => $thinNoneBorder,
                        'bottom' => $thinNoneBorder,
                        'left' => $thinNoneBorder,
                    ];
                    if ($x === $row) $border["top"]["style"] = "solid";
                    if ($x === $endRow) $border["bottom"]["style"] = "solid";
                    if ($y === $col) $border["left"]["style"] = "solid";
                    if ($y === $endCol) $border["right"]["style"] = "solid";
                    $styleArray["border"] = $border;
                }
                
                $cellInfo["styleArray"] = $styleArray;
                $this->rowsInfo[$x][$y] = $cellInfo;
            }
        }
    }

    protected function dataStoreToSpreadsheet()
    {
        $ds = $this->ds = $this->buildDatastore();
        if ($this->exportType === "excel") $sheet = $this->sheet;
        $option = $this->option;

        $emptyValue = Util::get($option, 'emptyValue', '-');
        $hideSubTotalRows = Util::get($option, 'hideSubTotalRows', false);
        $hideSubTotalColumns = Util::get($option, 'hideSubTotalColumns', false);
        $hideTotalRow = Util::get($option, 'hideTotalRow', false);
        $hideGrandTotalRow = Util::get($option, 'hideGrandTotalRow', $hideTotalRow);
        $hideTotalColumn = Util::get($option, 'hideTotalColumn', false);
        $hideGrandTotalColumn = Util::get($option, 'hideGrandTotalColumn', $hideTotalColumn);
        $showDataHeaders = Util::get($option, 'showDataHeaders', false);
        $this->mergeCells = Util::get($option, 'mergeCells', true);
        $showDuplicateRowHeaders = Util::get($option, 'showDuplicateRowHeaders', false);
        $showDuplicateColumnHeaders = Util::get($option, 'showDuplicateColumnHeaders', false);
        $mappedDataFieldZone = Util::get($option, ['map', 'dataFieldZone']);

        $colMetas = $ds->meta()['columns'];

        $pivotUtil = new \koolreport\pivot\PivotUtil($ds, $option);
        $pivotUtil->process();
        $fni = $pivotUtil->getFieldsNodesIndexes();
        $rowNodes = $fni['mappedRowNodes'];
        $colNodes = $fni['mappedColNodes'];
        $rowIndexes = $fni['rowIndexes'];
        $colIndexes = $fni['colIndexes'];
        $rowNodesInfo = $fni['rowNodesInfo'];
        $colNodesInfo = $fni['colNodesInfo'];
        $colFields = array_values($fni['colFields']);
        $rowFields = array_values($fni['rowFields']);
        $dataFields = array_values($fni['dataFields']);
        $mappedDataFields = $fni['mappedDataFields'];
        $mappedColFields = $fni['mappedColFields'];
        $mappedRowFields = $fni['mappedRowFields'];
        $mappedDataHeaders = $fni['mappedDataHeaders'];
        // $indexToMappedData = $fni['indexToMappedData'];
        $mappedDataFieldZoneValue = $fni['mappedDataFieldZoneValue'];

        $styleKey = "";
        if ($this->exportType === "excel") {
            $styleKey = "excel";
        } else if ($this->exportType === "bigspreadsheet") {
            $styleKey = "spreadsheet";
        }
        $pivotStyle = Util::get($option, "{$styleKey}Style", []);
        $ucfirstStyleKey = ucfirst($styleKey);
        $rowNodesStyle = $fni["rowNodes{$ucfirstStyleKey}Style"];
        $colNodesStyle = $fni["colNodes{$ucfirstStyleKey}Style"];
        $dataHeadersStyle = $fni["dataHeaders{$ucfirstStyleKey}Style"];
        // $indexToDataStyle = $fni["indexToData{$ucfirstStyleKey}Style"];

        $startCol = Util::get($option, 'startColumn', 1);
        $startRow = Util::get($option, 'startRow', 1);

        $this->rowsInfo = [];

        // Create data fields zone
        $template = $this->template;
        
        $row = $startRow;
        $col = $startCol;
        $value = $mappedDataFieldZoneValue;
        $fieldStyle = Util::get($pivotStyle, 'dataField', []);
        $fieldStyle = Util::map($fieldStyle, [$dataFields], []);
        if ($template === "pivottable") {
            $endRow = $row + count($colFields) - 1 + ($showDataHeaders ? 1 : 0);
            $endCol = $col + count($rowFields) - 1;
        } else if ($template === "pivotmatrix") {
            $endRow = $row + count($colFields) - 1;
            $endCol = $col + count($rowFields) - 1;
        }
        $this->toRowsInfo([
            "row" => $row, "col" => $col, "endRow" => $endRow, "endCol" => $endCol,
            "cellValue" => $value, "styleArray" => $fieldStyle,
        ]);

        $showColData = [];
        foreach ($colFields as $i => $f) {
            foreach ($colIndexes as $c => $j) {
                $nodeMark = $colNodesInfo[$j];
                $showColHeader = isset($nodeMark[$f]['numChildren']);
                $isTotal = isset($nodeMark[$f]['total']);
                $isSubTotal = $isTotal && $i > 0;
                $isGrandTotal = $isTotal && $i === 0;
                if (!isset($showColData[$c])) $showColData[$c] = true;
                if ($showColHeader && $hideSubTotalColumns && $isSubTotal)
                    $showColData[$c] = false;
                if ($showColHeader && $hideGrandTotalColumn && $isGrandTotal)
                    $showColData[$c] = false;
            }
        }

        //PivotMatrix: Create column and row fields zone
        if ($template === "pivotmatrix") {
            $c = count($colIndexes);
            $numSlippedColumns = 0;
            for ($n = 0; $n < $c; $n++)
                if (!$showColData[$n])
                    $numSlippedColumns += 1;
            $totalColNodeSpan = ($c - $numSlippedColumns) * count($dataFields);

            //Create column fields
            foreach ($colFields as $i => $f) {
                $row = $startRow + $i;
                $col = $startCol + count($rowFields);
                $endRow = $row + 0;
                $endCol = $col + $totalColNodeSpan - 1;
                $value = $mappedColFields[$f];
                $fieldStyle = Util::get($pivotStyle, 'columnField', []);
                $fieldStyle = Util::map($fieldStyle, [$colFields], []);

                $this->toRowsInfo([
                    "row" => $row, "col" => $col, "endRow" => $endRow, "endCol" => $endCol,
                    "cellValue" => $value, "styleArray" => $fieldStyle,
                ]);
            }

            $startRow = $startRow + count($colFields);

            //Create row fields            
            foreach ($rowFields as $i => $f) {
                $row = $startRow;
                $col = $startCol + $i;
                $endRow = $row + count($colFields) - 1 + ($showDataHeaders ? 1 : 0);
                $endCol = $col + 0;
                $value = $mappedRowFields[$f];
                $fieldStyle = Util::get($pivotStyle, 'rowField', []);
                $fieldStyle = Util::map($fieldStyle, [$rowFields], []);

                $this->toRowsInfo([
                    "row" => $row, "col" => $col, "endRow" => $endRow, "endCol" => $endCol,
                    "cellValue" => $value, "styleArray" => $fieldStyle,
                ]);
            }
        }

        // Create column headers zone
        foreach ($colFields as $i => $f) {
            foreach ($colIndexes as $c => $j) {
                $node = $colNodes[$j];
                $nodeMark = $colNodesInfo[$j];
                $nodeStyle = $colNodesStyle[$j];
                // print_r($nodeStyle); exit;
                $showColHeader = isset($nodeMark[$f]['numChildren']);
                if ($showColHeader && $showColData[$c]) {
                    $isTotal = isset($nodeMark[$f]['total']);
                    $numSlippedColumns = 0;
                    for ($n = 0; $n < $c; $n++)
                        if (!$showColData[$n])
                            $numSlippedColumns += count($dataFields);
                    $row = $startRow + $i;
                    $col = $startCol + count($rowFields)
                        + $c * count($dataFields) - $numSlippedColumns;
                    $rowspan = $isTotal ? $nodeMark[$f]['level'] : 1;
                    $colspan = $hideSubTotalColumns ?
                        $nodeMark[$f]['numLeaf'] : $nodeMark[$f]['numChildren'];
                    $endRow = $row + $rowspan - 1;
                    $endCol = $col + $colspan - 1;
                    $value = $node[$f];
                    $headerStyle = Util::get($nodeStyle, $f, []);

                    $this->toRowsInfo([
                        "row" => $row, "col" => $col, "endRow" => $endRow, "endCol" => $endCol,
                        "cellValue" => $value, "styleArray" => $headerStyle,
                        "duplicateValue" => $showDuplicateColumnHeaders,
                    ]);
                }
            }
        }

        // Create data headers zone
        if ($showDataHeaders) {
            $row = $startRow + count($colFields);
            $col = $startCol + count($rowFields);
            foreach ($colIndexes as $c => $j) {
                if (!$showColData[$c]) continue;
                foreach ($dataFields as $di => $df) {
                    $value = $mappedDataHeaders[$j][$df];
                    $headerStyle = Util::get($dataHeadersStyle, $df, []);

                    $this->toRowsInfo([
                        "row" => $row, "col" => $col, "endRow" => $row, "endCol" => $col,
                        "cellValue" => $value, "styleArray" => $headerStyle,
                    ]);

                    $col++;
                }
            }
            $startRow++;
        }

        if ($this->exportType === "excel") {
            $this->rowstoExcel($this->rowsInfo);
        } else if ($this->exportType === "bigspreadsheet") {
            $this->rowstoBigSpreadsheet($this->rowsInfo);
        }

        // Create row headers zone and data cells zone
        $maxLength = array_fill(0, count($rowFields), 0);
        $numSkippedRows = 0;
        $this->rowsInfo = [];
        foreach ($rowIndexes as $r => $i) {
            $firstX = null;

            $node = $rowNodes[$i];
            $nodeMark = $rowNodesInfo[$i];
            $nodeStyle = $rowNodesStyle[$i];
            $showRowData = true;
            
            // Create row headers 
            foreach ($rowFields as $j => $f) {
                $showRowHeader = isset($nodeMark[$f]['numChildren']);
                $isTotal = isset($nodeMark[$f]['total']);
                $isSubTotal = $isTotal && $j > 0;
                $isGrandTotal = $isTotal && $j === 0;
                if ($showRowHeader && $hideSubTotalRows && $isSubTotal)
                    $showRowData = false;
                if ($showRowHeader && $hideGrandTotalRow && $isGrandTotal)
                    $showRowData = false;
                if ($showRowHeader && !$showRowData) $numSkippedRows++;
                if ($showRowHeader && $showRowData) {
                    $row = $startRow + count($colFields) + $r - $numSkippedRows;
                    $col = $startCol + $j;
                    $rowspan = $hideSubTotalRows ?
                        $nodeMark[$f]['numLeaf'] : $nodeMark[$f]['numChildren'];
                    $colspan = $isTotal ? $nodeMark[$f]['level'] : 1;
                    $endRow = $row + $rowspan - 1;
                    $endCol = $col + $colspan - 1;
                    $value = $node[$f];
                    if ($maxLength[$j] < strlen($value)) {
                        $maxLength[$j] = strlen($value);
                    }
                    $headerStyle = Util::get($nodeStyle, $f, []);
                    if (!isset($firstX)) $firstX = $row;

                    $this->toRowsInfo([
                        "row" => $row, "col" => $col, "endRow" => $endRow, "endCol" => $endCol,
                        "cellValue" => $value, "styleArray" => $headerStyle,
                        "hAlignmentExcel" => Alignment::HORIZONTAL_LEFT,
                        "hAlignmentSpreadsheet" => CellAlignment::LEFT,
                        "duplicateValue" => $showDuplicateRowHeaders,
                    ]);
                }
            }

            if (!$showRowData) continue;

            // Create data cells 
            foreach ($colIndexes as $c => $j) {
                if (!$showColData[$c]) continue;

                $numSlippedColumns = 0;
                for ($n = 0; $n < $c; $n++)
                    if (!$showColData[$n])
                        $numSlippedColumns += count($dataFields);
                // $mappedDataRow = Util::get($indexToMappedData, [$i, $j], []);
                // $dataRowStyle = Util::get($indexToDataStyle, [$i, $j], []);
                list($mappedDataRow, $dataRowClass, $excelStyle, $spreadsheetStyle) =
                    $pivotUtil->getDataAttributesForCell($i, $j);
                $dataRowStyle = $this->exportType === "excel" ? $excelStyle : $spreadsheetStyle;
                foreach ($dataFields as $k => $df) {
                    $row = $startRow + count($colFields) + $r - $numSkippedRows;
                    $col = $startCol + count($rowFields) + $c * count($dataFields)
                        + $k - $numSlippedColumns;
                    if (isset($mappedDataRow[$df])) {
                        $value = $mappedDataRow[$df];
                        $colMeta = Util::get($colMetas, $df, []);
                        $type = Util::get($colMeta, 'type', 'string');
                        $format = $colMeta;
                        $formatCode = "";
                        switch ($type) {
                            case "number":
                                $decimals = Util::get($format, "decimals", 0);
                                $prefix = Util::get($format, "prefix", "");
                                $suffix = Util::get($format, "suffix", "");
                                $zeros = "";
                                for ($deIndex = 0; $deIndex < $decimals; $deIndex++) $zeros .= "0";
                                if ($decimals > 0) $zeros = ".$zeros";
                                $formatCode = "\"{$prefix}\"#,##0{$zeros}\"{$suffix}\"";
                                $formatCode = Util::get($format, "excelFormatCode", $formatCode);
                                break;
                            default:
                                $value = Util::format($value, $format);
                                break;
                        }
                    } else {
                        $value = $emptyValue;
                    }
                    $dataCellStyle = Util::get($dataRowStyle, $df, []);

                    $this->toRowsInfo([
                        "row" => $row, "col" => $col, "endRow" => $row, "endCol" => $col,
                        "cellValue" => $value, "styleArray" => $dataCellStyle,
                        "hAlignmentExcel" => Alignment::HORIZONTAL_RIGHT,
                        "hAlignmentSpreadsheet" => CellAlignment::RIGHT,
                    ]);
                }
            }

            if (isset($firstX) && isset($this->rowsInfo[$firstX])) {
                //Render the first row-header-data row to excel or big spreadsheet and unset it to save memory
                //since there might be tens of thousands of row-header-data rows
                $firstRowArr = [ $firstX => $this->rowsInfo[$firstX] ];
                if ($this->exportType === "excel") {
                    $this->rowstoExcel($firstRowArr);
                } else if ($this->exportType === "bigspreadsheet") {
                    $this->rowstoBigSpreadsheet($firstRowArr);
                }
                unset($this->rowsInfo[$firstX]);
            }
        }
        
        $columnAutoSize = Util::get($option, 'columnAutoSize', true);
        if ($columnAutoSize && $this->exportType === "excel") {
            for ($i = 0; $i < sizeof($maxLength); $i++) {
                $col = Coordinate::stringFromColumnIndex($startCol + $i);
                // $sheet->getColumnDimension($col)->setWidth($maxLength[$i]);
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }
    }

    public function saveDataStoreToExcel()
    {
        $this->exportType = "excel";
        $this->dataStoreToSpreadsheet();
    }

    public function saveDataStoreToBigSpreadsheet()
    {
        $this->exportType = "bigspreadsheet";
        $this->dataStoreToSpreadsheet();
    }

}
