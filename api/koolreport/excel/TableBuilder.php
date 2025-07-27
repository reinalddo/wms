<?php

namespace koolreport\excel;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use koolreport\core\Utility as Util;
use \PhpOffice\PhpSpreadsheet as ps;
use \PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Box\Spout\Common\Entity\Style\CellAlignment;

class TableBuilder extends WidgetBuilder
{
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

        $sheetInfo = $this->exportHandler->sheetInfo;
        $tableAutoName = 'table_' . $sheetInfo['tableAutoId']++;
        $tableName = Util::get($content, 'name', $tableAutoName);
        $sheetInfo['tablePositions'][$tableAutoName]
            = $sheetInfo['tablePositions'][$tableName]
            = $this->saveDataStoreToSheet();
        $sheetInfo['tableSheet'][$tableAutoName]
            = $sheetInfo['tableSheet'][$tableName]
            = $sheet->getTitle();
        $this->exportHandler->sheetInfo = $sheetInfo;

        return $sheetInfo['tablePositions'][$tableName];
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
        $option = $content;
        $option['startColumn'] = $colTrans;

        $this->option = $option;
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

        $this->rowGroup = Util::get($this->option, 'rowGroup', []);
        $sorts = [];
        foreach ($this->rowGroup as $field => $grInfo) {
            $direction = Util::get($grInfo, 'direction', 'asc');
            $sorts[$field] = $direction;
        }
        if (!empty($sorts)) $ds->sort($sorts);

        return $ds;
    }

    protected function buildExportColumnsAndMetas()
    {
        $colMetas = $this->colMetas;
        $optCols = Util::get($this->option, 'columns', array_keys($colMetas));
        $this->expColKeys = [];
        foreach ($optCols as $k => $v) {
            if (is_array($v)) {
                $col = $k;
            } elseif (is_string($v)) {
                $col = $v;
            }
            $colKeys = array_keys($colMetas);
            $colLabels = array_filter($colMetas, function ($cMeta) use ($col) {
                $label = Util::get($cMeta, 'label', null);
                if ($label === $col) {
                    return true;
                } else {
                    return false;
                }
            });
            if (isset($colMetas[$col])) {
                $colKey = $col;
                if (is_array($v)) 
                    $colMetas[$col] = array_merge($colMetas[$col], $v);
            } else if (!empty($colLabels)) {
                $colKey = array_keys($colLabels)[0];
            } else if (isset($colKeys[$col])) {
                $colKey = $colKeys[$col];
            } else {
                continue;
            }

            $this->expColKeys[] = $colKey;
        }
        $this->colMetas = $colMetas;
    }

    public function getFormatted($value, $meta)
    {
        $formatCode = "";
        $isDateTime = false;
        $type = Util::get($meta, 'type', 'string');
        switch ($type) {
            case "number":
                $decimals = Util::get($meta, "decimals", 0);
                $prefix = Util::get($meta, "prefix", "");
                $suffix = Util::get($meta, "suffix", "");
                $zeros = "";
                for ($i = 0; $i < $decimals; $i++) $zeros .= "0";
                if ($decimals > 0) $zeros = ".$zeros";
                $formatCode = "\"{$prefix}\"#,##0{$zeros}\"{$suffix}\"";
                $formatCode = Util::get($meta, "excelFormatCode", $formatCode);
                break;
            case "datetime":
                $datetimeFormat = Util::get($meta, "format", "Y-m-d H:i:s");
                $defaultFormat = 'YYYY-MM-DD HH:MM:SS';
                $isDateTime = true;
                break;
            case "date":
                $datetimeFormat = Util::get($meta, "format", "Y-m-d");
                $defaultFormat = 'YYYY-MM-DD';
                $isDateTime = true;
                break;
            case "time":
                $datetimeFormat = Util::get($meta, "format", "H:i:s");
                $defaultFormat = 'HH:MM:SS';
                $isDateTime = true;
                break;
            default:
                $value = Util::format($value, $meta);
                break;
        }
        if ($isDateTime) {
            $formatCode = Util::get($meta, "displayFormat", $defaultFormat);
            if ($date = \DateTime::createFromFormat($datetimeFormat, $value)) {
                $value = $date;
            }
            $value = ps\Shared\Date::PHPToExcel($value);
        }

        return [$value, $formatCode];
    }

    protected function getHFValueAndStyle($colKey, $pos, $footerValue = null)
    {
        $map = Util::get($this->map, $pos, []);
        $style = Util::get($this->tableStyle, $pos, []);
        $colMeta = $this->colMetas[$colKey];
        $label = Util::get($colMeta, 'label', $colKey);
        $args = $pos === 'footer' ? [$colKey, $footerValue] : [$colKey];
        $value = Util::map($map, $args, $label);

        $type = Util::get($colMeta, 'type', 'string');
        $styleArray = Util::map($style, $args, []);
        Util::init($styleArray, ['font', 'bold'], true);
        if ($type === 'number') {
            $alignment = $this->exportType === 'excel' ?
                ps\Style\Alignment::HORIZONTAL_RIGHT : CellAlignment::RIGHT;
            Util::init($styleArray, ['alignment', 'horizontal'], $alignment);
        }

        return [$value, $styleArray];
    }

    protected function buildTableHeaderFooter($pos)
    {
        $rgBuilder = $this->rowGroupBuilder;
        $hfRowsName = $pos . "Rows";
        $this->{$hfRowsName} = [];
        $showDefault = $pos === 'header' ? true : false;
        if (!Util::get($this->option, 'show' . ucfirst($pos), $showDefault)) return;

        //Build headers, footers for row group columns
        $emptyCell = [];
        $hfRow = array_fill(0, $this->startCol, $emptyCell);
        foreach ($rgBuilder->rowGroupFields as $grOrder => $grField) {
            if (!$rgBuilder->hasRowGroupTopBottom[$grOrder]) continue;

            $colKey = $grField;
            list($cellValue, $styleArray) = $this->getHFValueAndStyle($colKey, $pos);
            $cell = ["cellValue" => $cellValue, "styleArray" => $styleArray];
            $hfRow[] = $cell;
        }

        //Build headers, footers for table columns
        foreach ($this->expColKeys as $colKey) {
            $fValue = null;
            if ($pos === 'footer') {
                $colMeta = $this->colMetas[$colKey];
                $fValue = "";
                $method = strtolower(Util::get($colMeta, "footer"));
                if (in_array($method, ["sum", "avg", "min", "max", "mode"])) {
                    $fValue = Util::formatValue($this->ds->$method($colKey), $colMeta);
                }
                $footerText = Util::get($colMeta, "footerText");
                if ($footerText !== null) {
                    $fValue = str_replace("@value", $fValue, $footerText);
                }
                $footerMap = Util::get($this->map, 'footer', []);
                $fValue = Util::map($footerMap, [$colKey, $fValue], $fValue);
            }

            list($cellValue, $styleArray) = $this->getHFValueAndStyle($colKey, $pos, $fValue);
            $cell = ["cellValue" => $cellValue, "styleArray" => $styleArray];
            $hfRow[] = $cell;
        }

        $this->{$hfRowsName}[] = $hfRow;
    }

    protected function buildTableBodyRow($dataRow)
    {
        $rgBuilder = $this->rowGroupBuilder;
        $cellMap = Util::get($this->map, 'cell', []);
        $cellStyle = Util::get($this->tableStyle, 'cell', []);
        $emptyCell = [];
        $bodyRow = array_fill(0, $this->startCol + $rgBuilder->totalRowGroupColumns, $emptyCell);
        foreach ($this->expColKeys as $colKey) {
            $colMeta = Util::get($this->colMetas, $colKey, []);
            $value = Util::get($dataRow, $colKey);
            $value = Util::map($cellMap, [$colKey, $value, $dataRow], $value);
            list($value, $formatCode) = $this->getFormatted($value, $colMeta);

            $type = Util::get($colMeta, 'type', 'string');
            $styleArray = Util::map($cellStyle, [$colKey, $value, $dataRow], []);
            if ($type === 'number') {
                $alignment = $this->exportType === 'excel' ?
                    ps\Style\Alignment::HORIZONTAL_RIGHT : CellAlignment::RIGHT;
                Util::init($styleArray, ['alignment', 'horizontal'], $alignment);
            }

            if ($this->exportType === 'excel') {
                Util::init($styleArray, 'formatCode', $formatCode);
            }

            $cell = ["cellValue" => $value, "styleArray" => $styleArray];
            $bodyRow[] = $cell;
        }
        return $bodyRow;
    }

    protected function buildTableBody()
    {
        $bodyRows = [];
        $rgBuilder = $this->rowGroupBuilder;
        $this->ds->popStart();
        $prevDataRow = null;
        while (true) {
            $dataRow = $this->ds->pop();

            $bottomGroupRows = $rgBuilder->buildBottomGroupRows($prevDataRow, $dataRow);
            $bodyRows = array_merge($bodyRows, $bottomGroupRows);

            if (!isset($dataRow)) break;

            $topGroupRows = $rgBuilder->buildTopGroupRows($prevDataRow, $dataRow);
            $bodyRows = array_merge($bodyRows, $topGroupRows);

            $rgBuilder->setLastGroupValues();

            $bodyRow = $this->buildTableBodyRow($dataRow);
            $bodyRows[] = $bodyRow;

            $prevDataRow = $dataRow;
        }
        $this->bodyRows = &$bodyRows;
    }

    protected function tableToExcel()
    {
        $this->allRows = array_merge($this->headerRows, $this->bodyRows, $this->footerRows, $this->bottomHeaderRows);
        foreach ($this->allRows as $cellValues) {
            $expColOrder = -1 ;
            foreach ($cellValues as $cell) {
                $expColOrder++;
                if (empty($cell)) continue;
                
                $cellValue = Util::get($cell, 'cellValue');
                $styleArray = Util::get($cell, 'styleArray');
                $formatCode = Util::get($styleArray, 'formatCode');

                $cellAddress = Coordinate::stringFromColumnIndex($expColOrder)
                    . $this->rowOrder;
                $this->sheet->setCellValue($cellAddress, $cellValue);
                $style = $this->sheet->getStyle($cellAddress);
                if (!empty($formatCode)) {
                    $style->getNumberFormat()->setFormatCode($formatCode);
                }
                if (is_array($styleArray)) {
                    $style->applyFromArray($styleArray);
                }

            }
            $this->rowOrder++;
        }
    }

    protected function tableToBigSpreadsheet()
    {
        $this->allRows = array_merge($this->headerRows, $this->bodyRows, $this->footerRows, $this->bottomHeaderRows);

        foreach ($this->allRows as $cellValues) {
            $rowObj = [];
            foreach ($cellValues as $cell) {
                $cellValue = Util::get($cell, 'cellValue');
                $styleArray = Util::get($cell, 'styleArray');
                $cellObj = WriterEntityFactory::createCell(
                    $cellValue,
                    $this->getSpreadsheetStyleObj($styleArray)
                );
                $rowObj[] = $cellObj;
            }
            $rowFromValues = WriterEntityFactory::createRow($rowObj);
            $this->writer->addRow($rowFromValues);
        }
    }

    protected function mergeDuplicateRowsForCols()
    {
        $colOrder = $this->startCol;
        foreach ($this->expColKeys as $colKey) {
            if (! in_array($colKey, $this->removeDuplicateRowsForCols)) {
                $colOrder++;
                continue;
            }
            
            $rowOrder = $this->startRow;
            $lastCellValue = $lastCellAddress = null;
            for ($i = 0; $i < count($this->allRows) + 1; $i++) {
                $cellAddress = Coordinate::stringFromColumnIndex($colOrder)
                    . $rowOrder;
                $cellValue = $this->sheet->getCell($cellAddress)->getValue();
                if ($cellValue !== $lastCellValue) {
                    if ($lastCellValue !== null) {
                        $prevCellAddress = Coordinate::stringFromColumnIndex($colOrder)
                            . ($rowOrder - 1);
                        $this->sheet->mergeCells($lastCellAddress . ":" . $prevCellAddress);
                        $this->sheet->setCellValue($lastCellAddress, $lastCellValue);
                        $style = $this->sheet->getStyle($cellAddress);
                        $style->applyFromArray([
                            'alignment' => [
                                'vertical' => ps\Style\Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                    }
                    $lastCellValue = $cellValue;
                    $lastCellAddress = $cellAddress;
                }
                $rowOrder++;
            }
            $colOrder++;
        }
    }

    protected function buildRowGroups()
    {
        $rgBuilder = new TableRowGroupsBuilder();
        $rgBuilder->setProperties([
            'rowGroups' => $this->rowGroup,
            'ds' => $this->ds,
            'startCol' => $this->startCol,
            'expColKeys' => $this->expColKeys,
            'tableStyle' => $this->tableStyle,
        ])
            ->buildNumberOfGroupColumns()
            ->buildAggregates();
        $this->rowGroupBuilder = $rgBuilder;
    }

    public function saveDataStoreToSheet()
    {
        $this->exportType = 'excel';
        $this->ds = $this->buildDatastore();
        $this->colMetas = $this->ds->meta()['columns'];

        $option = $this->option;
        $this->tableStyle = Util::get($option, 'excelStyle', []);
        $this->map = Util::get($option, 'map', []);
        $this->startCol = Util::get($option, 'startColumn', 1);
        $this->startRow = Util::get($option, 'startRow', 1);
        $this->rowOrder = $this->startRow;

        $this->buildExportColumnsAndMetas();
        $this->buildRowGroups();
        $this->buildTableHeaderFooter('header');
        $this->buildTableBody();
        $this->buildTableHeaderFooter('footer');
        $this->buildTableHeaderFooter('bottomHeader');

        $this->tableToExcel();

        $this->removeDuplicateRowsForCols = Util::get($option, 'removeDuplicate');
        if (! empty($this->removeDuplicateRowsForCols)) {
            $this->mergeDuplicateRowsForCols();
        }

        // $sheet->calculateColumnWidths();
        for ($i = 0; $i < count($this->expColKeys); $i++) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            // $titlecolwidth = $sheet->getColumnDimension($col)->getWidth();
            // $sheet->getColumnDimension($col)->setWidth($titlecolwidth);
            $this->sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [
            'topLeft' => ($this->startCol) . ":" . ($this->startRow),
            'bottomRight' => ($this->startCol + count($this->expColKeys) - 1 + $this->rowGroupBuilder->totalRowGroupColumns) . ":" . ($this->rowOrder - 1),
        ];
    }

    public function saveDataStoreToBigSpreadsheet()
    {
        $this->exportType = 'bigspreadsheet';
        $this->ds = $this->buildDatastore();
        $this->colMetas = $this->ds->meta()['columns'];

        $option = $this->option;
        $this->tableStyle = Util::get($option, 'spreadsheetStyle', []);
        $this->map = Util::get($option, 'map', []);
        $this->startCol = Util::get($option, 'startColumn', 1);

        $this->buildExportColumnsAndMetas();
        $this->buildRowGroups();
        $this->buildTableHeaderFooter('header');
        $this->buildTableBody();
        $this->buildTableHeaderFooter('footer');
        $this->buildTableHeaderFooter('bottomHeader');

        $this->tableToBigSpreadsheet();
    }
}
