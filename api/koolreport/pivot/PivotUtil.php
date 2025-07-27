<?php

namespace koolreport\pivot;

use \koolreport\core\Utility as Util;

class PivotUtil
{
    protected $dataStore;
    protected $params;

    protected $measures;
    protected $rowDimension;
    protected $colDimension;
    protected $rowSort;
    protected $columnSort;
    protected $headerMap;
    protected $dataMap;
    protected $totalName;
    protected $hideTotalRow;
    protected $hideTotalColumn;
    protected $serverPaging = false;
    protected $paging;
    protected $template;

    public $FieldsNodesIndexes;
    public $showUsage = false;

    public function __construct(&$dataStore, $params = [])
    {
        $this->dataStore = &$dataStore;
        $this->params = $params;

        $this->rowDimension = Util::get($this->params, 'rowDimension', 'row');
        $this->colDimension = Util::get($this->params, 'colDimension', 'column');
        $this->rowSort = Util::get($this->params, 'rowSort', []);
        $this->columnSort = Util::get($this->params, 'columnSort', []);
        $this->headerMap = Util::get(
            $this->params,
            'headerMap',
            function ($v, $f) {
                return $v;
            }
        );
        $this->dataMap = Util::get($this->params, 'dataMap', null);
        $this->map = Util::get($this->params, 'map', []);
        $this->cssClass = Util::get($this->params, 'cssClass', []);
        $this->excelStyle = Util::get($this->params, 'excelStyle', []);
        $this->spreadsheetStyle = Util::get($this->params, 'spreadsheetStyle', []);
        $this->totalName = Util::get($this->params, 'totalName', 'Total');
        $this->hideTotalRow = Util::get($this->params, 'hideTotalRow', false);
        $this->hideGrandTotalRow = Util::get(
            $this->params,
            'hideGrandTotalRow',
            $this->hideTotalRow
        );
        $this->hideTotalColumn = Util::get($this->params, 'hideTotalColumn', false);
        $this->hideGrandTotalColumn = Util::get(
            $this->params,
            'hideGrandTotalColumn',
            $this->hideTotalColumn
        );

        //Get the measure field and settings in format
        $measures = [];
        $mSettings = Util::get($this->params, 'measures', []);
        $meta = $dataStore->meta();
        $cMetas = $this->cMetas = $meta['columns'];
        foreach ($mSettings as $cKey => $cValue) {
            if (gettype($cValue) == 'array') {
                $measures[$cKey] = $cValue;
            } else {
                $measures[$cValue] = isset($cMetas[$cValue]) ? $cMetas[$cValue] : null;
            }
        }
        if (empty($measures)) {
            $aggregates = Util::get($meta, 'pivotAggregates', null);
            if ($aggregates) {
                foreach ($aggregates as $df => $operators)
                    foreach ($operators as $op) {
                        $f = $df . ' - ' . $op;
                        $measures[$f] = Util::get($cMetas, $f, []);
                    }
            } else {
                $dataStore->popStart();
                $row = $dataStore->pop();
                $columns = !empty($row) ? array_keys($row) : [];
                foreach ($columns as $c) {
                    if ($cMetas[$c]['type'] !== 'dimension') {
                        $measures[$c] = $cMetas[$c];
                    }
                }
            }
        }
        // Util::prettyPrint($measures);
        $this->measures = $measures;
        $this->waitingFields = Util::get($this->params, 'waitingFields', []);
        // $this->process();
    }

    public function setPaging($serverPaging, $paging)
    {
        $this->serverPaging = $serverPaging;
        $this->paging = $paging;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    protected function sort(&$index, $sortInfo)
    {
        $compareFunc = function ($a, $b) use ($sortInfo) {
            foreach ($sortInfo as $k => $v)
                $$k = $v;
            $cmp = 0;
            $parentNode = [];
            foreach ($fields as $field) {
                $parentNode[$field] = '{{all}}';
            }

            foreach ($fields as $field) {
                $value1 = $nodes[$a][$field];
                $value2 = $nodes[$b][$field];
                $node1 = $node2 = $parentNode;
                $node1[$field] = $value1;
                $node2[$field] = $value2;
                if ($value1 === $value2) {
                    $parentNode[$field] = $value1;
                    continue;
                    // } else if ($value1 === '{{all}}') {
                } else if ($this->isRollupNodePart($value1)) {
                    return $sortTotalFirst ? -1 : 1;
                    // } else if ($value2 === '{{all}}') {
                } else if ($this->isRollupNodePart($value2)) {
                    return $sortTotalFirst ? 1 : -1;
                } else {
                    $cmp = is_numeric($value1) && is_numeric($value2) ?
                        $value1 - $value2 : strcmp($value1, $value2);
                    $sortField = isset($sort[$field]) ? $sort[$field] : null;
                    if (is_string($sortField)) {
                        $cmp = $sortField === 'desc' ? -$cmp : $cmp;
                    } else if (is_callable($sortField)) {
                        $cmp = $sortField($value2, $value1);
                    }
                }
                if ($cmp !== 0) {
                    break;
                }
            }
            $dataCmp = $cmp;
            foreach ($dataFields as $field) {
                if (isset($sort[$field]) && $sort[$field] !== 'ignore') {
                    $dataSortField = $field;
                    $dataSortDirection = $sort[$field];
                    break;
                }
            }

            $index1 = Util::get($nameToIndex, implode(' - ', $node1));
            $index2 = Util::get($nameToIndex, implode(' - ', $node2));
            if (
                isset($dataSortField) &&
                isset($dimIndexToData[$index1][$dataSortField]) &&
                isset($dimIndexToData[$index2][$dataSortField])
            ) {
                $sortValue1 = $dimIndexToData[$index1][$dataSortField];
                $sortValue2 = $dimIndexToData[$index2][$dataSortField];
                $diff = $sortValue1 - $sortValue2;
                if ($dataSortDirection === 'asc') {
                    $dataCmp = $diff;
                } else if ($dataSortDirection === 'desc') {
                    $dataCmp = -$diff;
                } else if (is_callable($dataSortDirection)) {
                    $dataCmp = $dataSortDirection($sortValue1, $sortValue2);
                }
            }
            return $dataCmp;
        };

        usort($index, $compareFunc);
    }

    protected function isRollupNodePart($nodePart)
    {
        if (in_array($nodePart, ["{{all}}", "{{sum}}", "{{count}}", "{{min}}", "{{max}}"]))
            return true;
        else
            return false;
    }

    protected function computeNodesInfo($nodes, $fields, $indexes)
    {
        $fieldInfo = array_fill_keys($fields, []);
        $nodesInfo = array_fill(0, count($nodes), $fieldInfo);
        // Util::prettyPrint($nodesInfo);
        $numChildren = array_fill_keys($fields, 1);
        $numLeaf = array_fill_keys($fields, 1);
        $childOrder = array_fill_keys($fields, 0);
        $nullNode = array_fill_keys($fields, null);
        $lastSameValueIndex = array_fill_keys($fields, $indexes[0]);
        // Add a last index for null node to build info (numChildren, etc) for the last node
        array_push($indexes, -1);
        $prevNode = $nullNode;
        //Loop through nodes already sorted by fields
        // echo "nodes = "; print_r($nodes); echo "<br>";
        // echo "indexes = "; print_r($indexes); echo "<br>";
        // echo "<br>";
        foreach ($indexes as $index) {
            $node = Util::get($nodes, $index, $nullNode);
            // echo "node = "; print_r($node); echo "<br>";
            // echo "prevNode = "; print_r($prevNode); echo "<br>";
            $seenTotalCell = false;
            //Loop through each field data of a node
            // $fCount = 0;
            foreach ($fields as $j => $f) {
                $nodeF = Util::get($node, $f);
                // $prevNodeF = Util::get($prevNode, $f);
                $isNodeDiff = false;
                for ($n = 0; $n <= $j; $n++) {
                    $fieldN = $fields[$n];
                    $nodeN = Util::get($node, $fieldN);
                    $prevNodeN = Util::get($prevNode, $fieldN);
                    if ($nodeN === null || $nodeN !== $prevNodeN) {
                        $isNodeDiff = true;
                        break;
                    }
                }
                // echo "field = " . $f . "<br>";
                // echo "isNodeDiff = "; var_dump($isNodeDiff); echo "<br>";
                // echo "<br>";

                if ($isNodeDiff) {
                    $lsvi = $lastSameValueIndex[$f];
                    // echo "lastSameValueIndex = " . $lsvi . "<br>";
                    // echo "lastSameValueIndex node value = " . $nodes[$lsvi][$f] . "<br>";
                    // if ($nodes[$lsvi][$f] !== '{{all}}') {
                    if (!$this->isRollupNodePart($nodes[$lsvi][$f])) {
                        // echo "set numChildren<br>";
                        $nodesInfo[$lsvi][$f]['numChildren'] = $numChildren[$f];
                        $nodesInfo[$lsvi][$f]['numLeaf'] = $numLeaf[$f];
                    }
                    $lastSameValueIndex[$f] = $index;
                    $numChildren[$f] = 1;
                    $numLeaf[$f] = 1;

                    $childOrder[$f] += 1;
                    $childOrders = '';
                    for ($k = 0; $k <= $j; $k++) {
                        $childOrders .= ($childOrder[$fields[$k]]) . ".";
                    }
                    $childOrders = substr($childOrders, 0, -1); //remove last "."
                    $nodesInfo[$index][$f]['childOrder'] = $childOrders;
                } else {
                    $numChildren[$f] += 1;
                    $numLeaf[$f] += 1;
                }

                $nodesInfo[$index][$f]['value'] = $nodeF;

                // if ($nodeF === '{{all}}') {
                if ($this->isRollupNodePart($nodeF)) {
                    $nodesInfo[$index][$f]['total'] = true;
                    $nodesInfo[$index]['hasTotal'] = true;
                    $childOrder[$f] = 0;
                }

                // if (! $seenTotalCell && $nodeF === '{{all}}') {
                if (!$seenTotalCell && $this->isRollupNodePart($nodeF)) {
                    $seenTotalCell = true;
                    $nodesInfo[$index][$f]['numChildren'] = 1;
                    $nodesInfo[$index][$f]['numLeaf'] = 1;
                    $nodesInfo[$index][$f]['level'] = count($fields) - $j;
                    Util::init($nodesInfo[$index][$f], 'childOrder', 1);
                    $nodesInfo[$index]['fieldOrder'] = $j - 1;

                    $prevField = $j > 0 ? $fields[$j - 1] : '';
                    $parent = Util::get($node, $prevField, null);
                    $prevParent = Util::get($prevNode, $prevField, null);
                    if ($parent !== $prevParent) continue;
                    for ($k = 0; $k < $j; $k++) {
                        $prevF = $fields[$k];
                        $numLeaf[$prevF] -= 1;
                    }
                }
            }
            if (!$seenTotalCell) {
                $nodesInfo[$index]['fieldOrder'] = count($fields) - 1;
            }
            $prevNode = $node;
        }
        array_pop($indexes);

        // echo "nodesInfo = "; Util::prettyPrint($nodesInfo);
        return $nodesInfo;
    }

    protected function toFunction($funcOrArr, $default = '{identical}')
    {
        $func = function ($v, $info) use ($funcOrArr, $default) {
            if ($default === '{identical}') $default = $v;
            if (is_array($funcOrArr)) {
                return isset($funcOrArr[$v]) ? $funcOrArr[$v] : $default;
            } elseif (is_callable($funcOrArr)) {
                return $funcOrArr($v, $info);
            }
            return $default;
        };
        return $func;
    }

    protected function getMappedFieldsAttributes($dimension, $fields)
    {
        $field = $dimension !== 'dataHeader' ? $dimension . 'Field' : 'dataHeader';
        $fieldMap = Util::get($this->map, $field, []);
        if ($dimension === 'dataHeader' && empty($fieldMap)) {
            $fieldMap = Util::get($this->map, 'dataField', []);
        }
        $fieldMap = $this->toFunction($fieldMap);
        $classMap = Util::get($this->cssClass, $field, []);
        $classMap = $this->toFunction($classMap, "");
        $excelMap = Util::get($this->excelStyle, $field, []);
        $excelMap = $this->toFunction($excelMap, []);
        $spreadsheetMap = Util::get($this->spreadsheetStyle, $field, []);
        $spreadsheetMap = $this->toFunction($spreadsheetMap, []);
        $fieldsInfo = [];
        foreach ($fields as $fi => $f) {
            $fieldsInfo[$f] = ['fieldOrder' => $fi];
        }
        $mappedFields = isset($fields[0]) && $fields[0] === 'root' ? [] :
            array_combine($fields, array_map($fieldMap, $fields, $fieldsInfo));
        $fieldsClass = isset($fields[0]) && $fields[0] === 'root' ? [] :
            array_combine($fields, array_map($classMap, $fields, $fieldsInfo));
        $fieldsExcelStyle = isset($fields[0]) && $fields[0] === 'root' ? [] :
            array_combine($fields, array_map($excelMap, $fields, $fieldsInfo));
        $fieldsSpreadsheetStyle = isset($fields[0]) && $fields[0] === 'root' ? [] :
            array_combine($fields, array_map($spreadsheetMap, $fields, $fieldsInfo));
        return [$mappedFields, $fieldsClass, $fieldsExcelStyle, $fieldsSpreadsheetStyle];
    }

    protected function getNodesAttributes($dimension, $nodes, $nodesInfo)
    {
        $nodeMap = Util::get($this->map, $dimension . 'Header', []);
        $totalName = $this->totalName;
        $nodeMap = function ($v, $info) use ($nodeMap, $totalName) {
            if ($v === '{{all}}') {
                if (is_callable($totalName)) $totalName = $totalName($v, $info);
                return $totalName;
            }
            if (is_array($nodeMap)) {
                return isset($nodeMap[$v]) ? $nodeMap[$v] : $v;
            } elseif (is_callable($nodeMap)) {
                return $nodeMap($v, $info);
            }
            return $v;
        };
        $classMap = Util::get($this->cssClass, $dimension . 'Header', []);
        $classMap = $this->toFunction($classMap, "");
        $excelMap = Util::get($this->excelStyle, $dimension . 'Header', []);
        $excelMap = $this->toFunction($excelMap, []);
        $spreadsheetMap = Util::get($this->spreadsheetStyle, $dimension . 'Header', []);
        $spreadsheetMap = $this->toFunction($spreadsheetMap, []);
        $mappedNodes = [];
        $nodesClass = [];
        $nodesExcelStyle = [];
        $nodesSpreadsheetStyle = [];
        if ($dimension !== "data") {
            foreach ($nodes as $i => $node) {
                $fields = array_keys($node);
                foreach ($fields as $fi => $f) {
                    $nodeInfo[$f] = Util::get($nodesInfo, [$i, $f]);
                    $nodeInfo[$f]['fieldName'] = $f;
                    $nodeInfo[$f]['fieldOrder'] = $fi;
                    $nodeInfo[$f][$dimension] = $nodesInfo[$i];
                }
                $mappedNodes[$i] = array_map($nodeMap, $node, $nodeInfo);
                $mappedNodes[$i] = array_combine($fields, $mappedNodes[$i]);
                $nodesClass[$i] = array_map($classMap, $node, $nodeInfo);
                $nodesClass[$i] = array_combine($fields, $nodesClass[$i]);
                $nodesExcelStyle[$i] = array_map($excelMap, $node, $nodeInfo);
                $nodesExcelStyle[$i] = array_combine($fields, $nodesExcelStyle[$i]);
                $nodesSpreadsheetStyle[$i] = array_map($spreadsheetMap, $node, $nodeInfo);
                $nodesSpreadsheetStyle[$i] = array_combine($fields, $nodesSpreadsheetStyle[$i]);
            }
        } else {
            foreach ($nodes as $i => $node) {
                $nodeInfo = [];
                $fields = $this->dataFields;
                foreach ($fields as $dfi => $df) {
                    $nodeInfo[$df] = [];
                    $nodeInfo[$df]['fieldName'] = $df;
                    $nodeInfo[$df]['fieldOrder'] = $dfi;
                    $nodeInfo[$df]["column"] = $nodesInfo[$i];
                }
                $mappedNodes[$i] = array_map($nodeMap, $fields, $nodeInfo);
                $mappedNodes[$i] = array_combine($fields, $mappedNodes[$i]);
                $nodesClass[$i] = array_map($classMap, $fields, $nodeInfo);
                $nodesClass[$i] = array_combine($fields, $nodesClass[$i]);
                $nodesExcelStyle[$i] = array_map($excelMap, $fields, $nodeInfo);
                $nodesExcelStyle[$i] = array_combine($fields, $nodesExcelStyle[$i]);
                $nodesSpreadsheetStyle[$i] = array_map($spreadsheetMap, $fields, $nodeInfo);
                $nodesSpreadsheetStyle[$i] = array_combine($fields, $nodesSpreadsheetStyle[$i]);
            }
        }
        return [$mappedNodes, $nodesClass, $nodesExcelStyle, $nodesSpreadsheetStyle];
    }

    protected function getDataAttributes($indexToData, $rowNodesInfo, $colNodesInfo)
    {
        $cMetas = $this->dataStore->meta()['columns'];
        $cellMap = Util::get($this->map, 'dataCell', function ($v, $info) use ($cMetas) {
            $df = $info['fieldName'];
            return Util::format($v, Util::get($cMetas, $df, []));
        });
        $cellMap = $this->toFunction($cellMap);
        $classMap = Util::get($this->cssClass, 'dataCell', []);
        $classMap = $this->toFunction($classMap, "");
        $excelMap = Util::get($this->excelStyle, 'dataCell', []);
        $excelMap = $this->toFunction($excelMap, []);
        $spreadsheetMap = Util::get($this->spreadsheetStyle, 'dataCell', []);
        $spreadsheetMap = $this->toFunction($spreadsheetMap, []);
        $dataFields = $this->dataFields;
        $indexToMappedData = [];
        $indexToDataClass = [];
        $indexToDataExcelStyle = [];
        $indexToDataSpreadsheetStyle = [];

        // Util::prettyPrint($indexToData); exit;
        foreach ($indexToData as $ri => $cis) {
            $rowNodeInfo = $rowNodesInfo[$ri];
            $indexToMappedData[$ri] = [];
            $indexToDataClass[$ri] = [];
            $indexToDataExcelStyle[$ri] = [];
            foreach ($cis as $ci => $dataNode) {
                $colNodeInfo = $colNodesInfo[$ci];
                $nodeInfo = [
                    'row' => $rowNodeInfo,
                    'column' => $colNodeInfo
                ];
                $node = array_slice($dataNode, 0, count($dataFields));
                $cellInfo = [];
                foreach ($dataFields as $di => $df) {
                    $cellInfo[$df] = $nodeInfo;
                    $cellInfo[$df]['rowIndex'] = $ri;
                    $cellInfo[$df]['columnIndex'] = $ci;
                    $cellInfo[$df]['indexToData'] = &$indexToData;
                    $cellInfo[$df]['fieldName'] = $df;
                    $cellInfo[$df]['fieldOrder'] = $di;
                    $cellInfo[$df]['formattedValue'] =
                        Util::format(
                            Util::get($node, $df, null),
                            Util::get($cMetas, $df, [])
                        );
                }
                $mappedNode = array_map($cellMap, $node, $cellInfo);
                $indexToMappedData[$ri][$ci] = array_combine($dataFields, $mappedNode);
                $nodeClass = array_map($classMap, $node, $cellInfo);
                $indexToDataClass[$ri][$ci] = array_combine($dataFields, $nodeClass);
                $nodeExcelStyle = array_map($excelMap, $node, $cellInfo);
                $indexToDataExcelStyle[$ri][$ci] = array_combine($dataFields, $nodeExcelStyle);
                $nodeSpreadsheetStyle = array_map($spreadsheetMap, $node, $cellInfo);
                $indexToDataSpreadsheetStyle[$ri][$ci] = array_combine($dataFields, $nodeSpreadsheetStyle);
            }
        }



        return [$indexToMappedData, $indexToDataClass, $indexToDataExcelStyle, $indexToDataSpreadsheetStyle];
    }

    protected function computeDataCellMaps()
    {
        $cMetas = $this->dataStore->meta()['columns'];
        $cellMap = Util::get($this->map, 'dataCell', function ($v, $info) use ($cMetas) {
            $df = $info['fieldName'];
            return Util::format($v, Util::get($cMetas, $df, []));
        });
        $this->cellMap = $this->toFunction($cellMap);
        // echo "this->cellMap = "; print_r($this->cellMap);
        $classMap = Util::get($this->cssClass, 'dataCell', []);
        $this->classMap = $this->toFunction($classMap, "");
        $excelMap = Util::get($this->excelStyle, 'dataCell', []);
        $this->excelMap = $this->toFunction($excelMap, []);
        $spreadsheetMap = Util::get($this->spreadsheetStyle, 'dataCell', []);
        $this->spreadsheetMap = $this->toFunction($spreadsheetMap, []);
    }

    public function getDataAttributesForCell($ri, $ci)
    {
        $cMetas = $this->dataStore->meta()['columns'];
        // $cellMap = Util::get($this->map, 'dataCell', function ($v, $info) use ($cMetas) {
        //     // echo "info = "; Util::prettyPrint($info); echo "<br>";
        //     $df = $info['fieldName'];
        //     return Util::format($v, Util::get($cMetas, $df, []));
        // });
        // $cellMap = $this->toFunction($cellMap);
        // $classMap = Util::get($this->cssClass, 'dataCell', []);
        // $classMap = $this->toFunction($classMap, "");
        // $excelMap = Util::get($this->excelStyle, 'dataCell', []);
        // $excelMap = $this->toFunction($excelMap, []);
        // $spreadsheetMap = Util::get($this->spreadsheetStyle, 'dataCell', []);
        // $spreadsheetMap = $this->toFunction($spreadsheetMap, []);

        $dataFields = $this->dataFields;
        $rowNodeInfo = $this->isBunTemplate ? $this->rowNodesInfoBun[$ri] : $this->rowNodesInfo[$ri];
        $colNodeInfo = $this->colNodesInfo[$ci];
        $nodeInfo = [
            'row' => $rowNodeInfo,
            'column' => $colNodeInfo
        ];
        $dataNode = Util::get($this->indexToData, [$ri, $ci], []);
        $node = array_slice($dataNode, 0, count($dataFields));
        $cellInfo = [];
        foreach ($dataFields as $di => $df) {
            $cellInfo[$df] = $nodeInfo;
            $cellInfo[$df]['rowIndex'] = $ri;
            $cellInfo[$df]['columnIndex'] = $ci;
            // $cellInfo[$df]['indexToData'] = &$indexToData;
            $cellInfo[$df]['fieldName'] = $df;
            $cellInfo[$df]['fieldOrder'] = $di;
            $cellInfo[$df]['formattedValue'] =
                Util::format(
                    Util::get($node, $df, null),
                    Util::get($cMetas, $df, [])
                );
        }
        $mappedNode = array_map($this->cellMap, $node, $cellInfo);
        $mappedData = array_combine($dataFields, $mappedNode);
        $nodeClass = array_map($this->classMap, $node, $cellInfo);
        $dataClass = array_combine($dataFields, $nodeClass);
        $nodeExcelStyle = array_map($this->excelMap, $node, $cellInfo);
        $excelStyle = array_combine($dataFields, $nodeExcelStyle);
        $nodeSpreadsheetStyle = array_map($this->spreadsheetMap, $node, $cellInfo);
        $spreadsheetStyle = array_combine($dataFields, $nodeSpreadsheetStyle);

        return [$mappedData, $dataClass, $excelStyle, $spreadsheetStyle];
    }

    public function getDataForCell($ri, $ci)
    {
        return Util::get($this->indexToData, [$ri, $ci], []);
    }

    protected function nodeNamesToNodes($nodeNames, $fields, $fieldDelimiter)
    {
        $nodes = [];
        foreach ($nodeNames as $name) {
            $name = explode($fieldDelimiter, $name);
            $node = [];
            foreach ($fields as $i => $f) {
                $node[$f] = $name[$i];
            }
            $name = implode($fieldDelimiter, $node);
            $nodes[$name] = $node;
        }
        $nodes = array_values($nodes);
        return $nodes;
    }

    protected function computeIndexes()
    {
        $dataStore = &$this->dataStore;
        $meta = $this->dataStore->meta();
        $pivotFormat = Util::get($meta, 'pivotFormat', 'pivot');
        $cMetas = $this->cMetas;
        if ($this->showUsage) echo "PivotUtil computeIndexes PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";
        if ($pivotFormat === 'pivot2D' && $dataStore->count() > 0) {
            // echo "pivot2D<br>";
            $rowNodes = $colNodes = [];
            $rowIndexToData = [];
            $colIndexToData = [];
            $indexToData = [];
            $rowFields = !empty($meta['pivotRows']) ? $meta['pivotRows'] :  ['root'];
            $colFields = !empty($meta['pivotColumns']) ? $meta['pivotColumns'] :  ['root'];
            $fieldDelimiter = $meta['pivotFieldDelimiter'];
            $dataFields = $this->dataFields = array_keys($this->measures);

            $dataStore->popStart();
            while ($dataRow = $dataStore->pop()) {
                if (empty($colNodes)) {
                    $nodeNames = array_slice(array_keys($dataRow), 1);
                    $colNodes = $this->nodeNamesToNodes(
                        $nodeNames,
                        $colFields,
                        $fieldDelimiter
                    );
                }
                array_push($rowNodes, $dataRow['label']);
                $rowIndex = count($rowNodes) - 1;
                foreach ($colNodes as $colIndex => $colNode) {
                    $newDataRow = [];
                    $colNode = implode($fieldDelimiter, $colNode);
                    foreach ($dataFields as $df) {
                        if (isset($dataRow[$colNode . $fieldDelimiter . $df]))
                            $newDataRow[$df] = $dataRow[$colNode . $fieldDelimiter . $df];
                    }
                    if ($colIndex === 0) {
                        $rowIndexToData[$rowIndex] = $newDataRow;
                    }
                    if ($rowIndex === 0) {
                        $colIndexToData[$colIndex] = $newDataRow;
                    }
                    // $indexToData[$rowIndex][$colIndex] = $newDataRow;
                }
            }
            $rowNodes = $this->nodeNamesToNodes($rowNodes, $rowFields, $fieldDelimiter);
            $nameToIndexRow = [];
            foreach ($rowNodes as $i => $node) {
                $nameToIndexRow[implode(' - ', $node)] = $i;
            }
            $nameToIndexCol = [];
            foreach ($colNodes as $i => $node) {
                $nameToIndexCol[implode(' - ', $node)] = $i;
            }
        } else {
            $rowDimension = isset($cMetas[$this->rowDimension]) ?
                $this->rowDimension : null;
            $colDimension = isset($cMetas[$this->colDimension]) ?
                $this->colDimension : null;

            $rowNodes = isset($rowDimension) ?
                $cMetas[$rowDimension]['index'] : null;
            $colNodes = isset($colDimension) ?
                $cMetas[$colDimension]['index'] : null;
            if (empty($rowNodes) || empty($rowNodes[0])) {
                $rowNodes = array(array('root' => '{{all}}'));
            }
            if (empty($colNodes) || empty($colNodes[0])) {
                $colNodes = array(array('root' => '{{all}}'));
            }

            // $rowFields = array_keys($rowNodes[0]);
            // $colFields = array_keys($colNodes[0]);
            $rowFields = !empty($meta['pivotRows']) ? $meta['pivotRows'] :  ['root'];
            $colFields = !empty($meta['pivotColumns']) ? $meta['pivotColumns'] :  ['root'];
            $dataFields = $this->dataFields = array_keys($this->measures);

            $nameToIndexRow = [];
            foreach ($rowNodes as $i => $node) {
                $nameToIndexRow[implode(' - ', $node)] = $i;
            }

            $nameToIndexCol = [];
            foreach ($colNodes as $i => $node) {
                $nameToIndexCol[implode(' - ', $node)] = $i;
            }

            $rowIndexToData = [];
            $colIndexToData = [];
            $indexToData = [];

            if ($this->showUsage) echo "PivotUtil computeIndexes PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";

            $dataStore->popStart();
            while ($dataRow = $dataStore->pop()) {
                $rowIndex = (int) Util::get($dataRow, $rowDimension, 0);
                $colIndex = (int) Util::get($dataRow, $colDimension, 0);
                if (isset($rowDimension) && $colIndex === 0) {
                    $rowIndexToData[$rowIndex] = $dataRow;
                }

                if (isset($colDimension) && $rowIndex === 0) {
                    $colIndexToData[$colIndex] = $dataRow;
                }

                // $indexToData[$rowIndex][$colIndex] = $dataRow;
                unset($dataRow);
            }
            // for ($i = 0; $i < count($rowNodes); $i++)
            //     for ($j = 0; $j < count($colNodes); $j++)
            //         Util::init($indexToData, [$i, $j], []);
            // Util::prettyPrint($indexToData); exit;
            // echo 'dataFields = '; Util::prettyPrint($dataFields);
        }
        // echo "PivotUtil computeIndexes PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";
        // echo $this->dataStore->count() . "<br>";
        // $this->dataStore->rows = null;
        // $this->dataStore->rows = [];
        // echo $this->dataStore->count() . "<br>";
        // gc_collect_cycles();
        if ($this->showUsage) echo "PivotUtil computeIndexes PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";
        return [
            $rowFields, $colFields, $dataFields, $rowNodes, $colNodes,
            $nameToIndexRow, $nameToIndexCol, $rowIndexToData, $colIndexToData, $indexToData
        ];
    }

    protected function computeDataIndexes($pagedRowIndexes)
    {
        $pagedRowIndexes = array_flip($pagedRowIndexes);

        $dataStore = &$this->dataStore;
        $cMetas = $this->cMetas;
        $meta = $this->dataStore->meta();
        $pivotFormat = Util::get($meta, 'pivotFormat', 'pivot');
        if ($pivotFormat === 'pivot2D' && $dataStore->count() > 0) {
            // echo "pivot2D<br>";
            $rowNodes = $colNodes = [];
            $indexToData = [];
            $rowFields = !empty($meta['pivotRows']) ? $meta['pivotRows'] :  ['root'];
            $colFields = !empty($meta['pivotColumns']) ? $meta['pivotColumns'] :  ['root'];
            $fieldDelimiter = $meta['pivotFieldDelimiter'];
            $dataFields = $this->dataFields = array_keys($this->measures);

            $dataStore->popStart();
            while ($dataRow = $dataStore->pop()) {
                if (empty($colNodes)) {
                    $nodeNames = array_slice(array_keys($dataRow), 1);
                    $colNodes = $this->nodeNamesToNodes(
                        $nodeNames,
                        $colFields,
                        $fieldDelimiter
                    );
                }
                array_push($rowNodes, $dataRow['label']);
                $rowIndex = count($rowNodes) - 1;
                foreach ($colNodes as $colIndex => $colNode) {
                    $newDataRow = [];
                    $colNode = implode($fieldDelimiter, $colNode);
                    foreach ($dataFields as $df) {
                        if (isset($dataRow[$colNode . $fieldDelimiter . $df]))
                            $newDataRow[$df] = $dataRow[$colNode . $fieldDelimiter . $df];
                    }
                    if (isset($pagedRowIndexes[$rowIndex])) $indexToData[$rowIndex][$colIndex] = $newDataRow;
                }
            }
            $rowNodes = $this->nodeNamesToNodes($rowNodes, $rowFields, $fieldDelimiter);
        } else {
            $rowDimension = isset($cMetas[$this->rowDimension]) ?
                $this->rowDimension : null;
            $colDimension = isset($cMetas[$this->colDimension]) ?
                $this->colDimension : null;

            $indexToData = [];

            $dataStore->popStart();
            while ($dataRow = $dataStore->pop()) {
                $rowIndex = (int) Util::get($dataRow, $rowDimension, 0);
                $colIndex = (int) Util::get($dataRow, $colDimension, 0);

                if (isset($pagedRowIndexes[$rowIndex])) $indexToData[$rowIndex][$colIndex] = $dataRow;
                unset($dataRow);
            }
        }
        return $indexToData;
    }

    public function process()
    {
        if ($this->showUsage) echo "PivotUtil begin process() PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";
        $this->isBunTemplate = $isBunTemplate
            = $this->template === 'PivotTable-Bun' || $this->template === 'PivotMatrix-Bun';

        if (!$this->dataStore) {
            return [];
        }
        $cMetas = $this->cMetas;
        list(
            $rowFields, $colFields, $dataFields, $rowNodes, $colNodes,
            $nameToIndexRow, $nameToIndexCol, $rowIndexToData, $colIndexToData, $indexToData
        )
            = $this->computeIndexes();

        if ($this->showUsage) echo "PivotUtil middle 0 process() PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";

        $numRows = count($rowNodes);


        $rowNodesInfo = $rowNodesClass = $rowNodesExcelStyle = $rowNodesSpreadsheetStyle
            = $rowNodesInfoBun = $rowNodesClassBun = $rowNodesExcelStyleBun = $rowNodesSpreadsheetStyleBun = [];

        $numNodes = count($rowNodes);
        if ($numNodes === 0) $numNodes = 1;
        $rowIndexes = $rowIndexesBun = range(0, $numNodes - 1);
        $numNodes = count($colNodes);
        if ($numNodes === 0) $numNodes = 1;
        $colIndexes = range(0, $numNodes - 1);
        $rowSortInfo = [
            'nodes' => $rowNodes,
            'fields' => $rowFields,
            'nameToIndex' => $nameToIndexRow,
            'dimIndexToData' => $rowIndexToData,
            'sort' => $this->rowSort,
            'dataFields' => $dataFields,
            'sortTotalFirst' => false
        ];

        if ($isBunTemplate) {
            $rowSortInfo['sortTotalFirst'] = true;
            $this->sort($rowIndexesBun, $rowSortInfo);
        } else {
            $this->sort($rowIndexes, $rowSortInfo);
        }

        //Push the grand total index to the end instead of the beginning
        // $grandTotalIndex = array_shift($rowIndexesBun);
        // array_push($rowIndexesBun, $grandTotalIndex);

        $colSortInfo = [
            'nodes' => $colNodes,
            'fields' => $colFields,
            'nameToIndex' => $nameToIndexCol,
            'dimIndexToData' => $colIndexToData,
            'sort' => $this->columnSort,
            'dataFields' => $dataFields,
            'sortTotalFirst' => false
        ];
        $this->sort($colIndexes, $colSortInfo);

        if ($this->hideGrandTotalRow) {
            array_pop($rowIndexes); //remove grand total row node
            array_shift($rowIndexesBun); //remove grand total row node
        }

        if ($this->serverPaging) {
            $pageNum = Util::get($this->paging, 'page', 1);
            $pageSize = Util::get($this->paging, 'size', 10);
            $startRow = $pageSize * ($pageNum - 1);
            if ($isBunTemplate) {
                //if bun template and page > 1 we need to add the last row of previous page to compute node info later
                if ($startRow > 0) $rowIndexesBun = array_slice($rowIndexesBun, $startRow - 1, $pageSize + 1);
                else $rowIndexesBun = array_slice($rowIndexesBun, $startRow, $pageSize);
            } else {
                $rowIndexes = array_slice($rowIndexes, $startRow, $pageSize);
            }
        }

        if ($this->showUsage) echo "PivotUtil middle 1 process() PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";

        if ($isBunTemplate) {
            // echo "compute rowNodesInfoBun Bun template<br>";
            $rowNodesInfoBun = $this->computeNodesInfo($rowNodes, $rowFields, $rowIndexesBun);
            $this->rowNodesInfoBun = &$rowNodesInfoBun;
            if ($this->serverPaging && $startRow > 0) array_shift($rowIndexesBun);
            $pageRowNodes = [];
            foreach ($rowIndexesBun as $ri) $pageRowNodes[$ri] = $rowNodes[$ri];
            $rowNodes = $pageRowNodes;
            // Util::prettyPrint($this->rowNodesInfoBun);
        } else {
            $rowNodesInfo = $this->computeNodesInfo($rowNodes, $rowFields, $rowIndexes);
            $this->rowNodesInfo = &$rowNodesInfo;
            $pageRowNodes = [];
            foreach ($rowIndexes as $ri) $pageRowNodes[$ri] = $rowNodes[$ri];
            $rowNodes = $pageRowNodes;
        }

        $colNodesInfo = $this->computeNodesInfo($colNodes, $colFields, $colIndexes);
        $this->colNodesInfo = &$colNodesInfo;

        if ($this->hideGrandTotalColumn) {
            array_pop($colIndexes); //remove grand total column node
        }

        $numDf = count($dataFields) > 0 ? count($dataFields) : 1;
        foreach ($colNodesInfo as $i => $mark) {
            foreach ($mark as $f => $fInfo) {
                if (!isset($fInfo['numChildren'])) continue;
                $colNodesInfo[$i][$f]['numChildren'] *= $numDf;
                $colNodesInfo[$i][$f]['numLeaf'] *= $numDf;
            }
        }

        $indexToData = $this->computeDataIndexes($rowIndexes);
        $this->indexToData = &$indexToData;
        if ($this->showUsage) echo "PivotUtil middle 1.1 process() PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";

        $totalName = $this->totalName;
        $headerMap = $this->headerMap;
        $headerMap = function ($v, $f) use ($headerMap, $totalName) {
            if ($v === '{{all}}') {
                if (is_callable($totalName)) $totalName = $totalName($v, []);
                return $totalName;
            }
            if (is_array($headerMap)) {
                return isset($headerMap[$v]) ? $headerMap[$v] : $v;
            }
            return $headerMap($v, $f);
        };

        $waitingFields = array_keys($this->waitingFields);
        $mappedDataFields = array_combine(
            $dataFields,
            array_map($headerMap, $dataFields, [], [])
        );
        $mappedColFields = $colFields[0] !== 'root' ? array_combine(
            $colFields,
            array_map($headerMap, $colFields, [], [])
        ) : [];
        $mappedRowFields = $rowFields[0] !== 'root' ? array_combine(
            $rowFields,
            array_map($headerMap, $rowFields, [], [])
        ) : [];
        $mappedWaitingFields = array_combine(
            $waitingFields,
            array_map($headerMap, $waitingFields, [], [])
        );

        $mappedRowNodes = $mappedRowNodesBun = [];
        foreach ($rowNodes as $i => $node) {
            if ($isBunTemplate) {
                $mappedRowNodesBun[$i] = array_combine(
                    $rowFields,
                    array_map($headerMap, $node, $rowFields)
                );
            } else {
                $mappedRowNodes[$i] = array_combine(
                    $rowFields,
                    array_map($headerMap, $node, $rowFields)
                );
            }
        }
        $mappedColNodes = [];
        foreach ($colNodes as $i => $node) {
            $mappedColNodes[$i] = array_combine(
                $colFields,
                array_map($headerMap, $node, $colFields)
            );
            $mappedDataHeaders[$i] = $mappedDataFields;
        }

        list($mappedFields, $rowFieldsClass, $rowFieldsExcelStyle, $rowFieldsSpreadsheetStyle) =
            $this->getMappedFieldsAttributes('row', $rowFields);
        if (isset($this->map['rowField'])) $mappedRowFields = $mappedFields;
        list($mappedFields, $colFieldsClass, $colFieldsExcelStyle, $colFieldsSpreadsheetStyle) =
            $this->getMappedFieldsAttributes('column', $colFields);
        if (isset($this->map['columnField'])) $mappedColFields = $mappedFields;
        list($mappedFields, $dataFieldsClass, $dataFieldsExcelStyle, $dataFieldsSpreadsheetStyle) =
            $this->getMappedFieldsAttributes('data', $dataFields);
        if (isset($this->map['dataField'])) $mappedDataFields = $mappedFields;
        list($mappedFields, $waitingFieldsClass, $waitingFieldsExcelStyle, $waitingFieldsSpreadsheetStyle) =
            $this->getMappedFieldsAttributes('waiting', $waitingFields);
        if (isset($this->map['waitingField'])) $mappedWaitingFields = $mappedFields;
        // list($mappedFields, $dataHeadersClass, $dataHeadersExcelStyle, $dataHeadersSpreadsheetStyle) = 
        //     $this->getMappedFieldsAttributes('dataHeader', $dataFields);
        // if (isset($this->map['dataHeader'])) $mappedDataHeaders = $mappedFields;

        if ($this->showUsage) echo "PivotUtil middle 2 process() PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";

        if ($isBunTemplate) {
            list($mappedNodes, $rowNodesClassBun, $rowNodesExcelStyleBun, $rowNodesSpreadsheetStyleBun) =
                $this->getNodesAttributes('row', $rowNodes, $rowNodesInfoBun);
            if (isset($this->map['rowHeader'])) $mappedRowNodesBun = $mappedNodes;
        } else {
            // echo "count rowNodes = " . count($rowNodes) . "<br>";
            if ($this->showUsage) echo "PivotUtil middle 2.1 process() PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";
            list($mappedNodes, $rowNodesClass, $rowNodesExcelStyle, $rowNodesSpreadsheetStyle) =
                $this->getNodesAttributes('row', $rowNodes, $rowNodesInfo);
            if (isset($this->map['rowHeader'])) $mappedRowNodes = $mappedNodes;
            if ($this->showUsage) echo "PivotUtil middle 2.2 process() PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";
        }

        list($mappedNodes, $colNodesClass, $colNodesExcelStyle, $colNodesSpreadsheetStyle) =
            $this->getNodesAttributes('column', $colNodes, $colNodesInfo);
        if (isset($this->map['columnHeader'])) $mappedColNodes = $mappedNodes;
        // echo "colNodesExcelStyle = "; Util::prettyPrint($colNodesExcelStyle); exit;
        $this->dataFields = $dataFields;
        list($mappedNodes, $dataHeadersClass, $dataHeadersExcelStyle, $dataHeadersSpreadsheetStyle) =
            $this->getNodesAttributes('data', $colNodes, $colNodesInfo);
        if (isset($this->map['dataHeader'])) $mappedDataHeaders = $mappedNodes;
        // echo "mappedDataHeaders = "; Util::prettyPrint($mappedDataHeaders);
        // echo "dataHeadersClass = "; Util::prettyPrint($dataHeadersClass);
        // echo "dataHeadersExcelStyle = "; Util::prettyPrint($dataHeadersExcelStyle);
        // echo "dataHeadersSpreadsheetStyle = "; Util::prettyPrint($dataHeadersSpreadsheetStyle);

        $mappedDataFieldZone = Util::get($this->map, 'dataFieldZone');
        $mappedDataFieldZoneValue = implode(' | ', $mappedDataFields);
        if (isset($mappedDataFieldZone)) {
            if (is_callable($mappedDataFieldZone)) $mappedDataFieldZoneValue = $mappedDataFieldZone($dataFields);
            else $mappedDataFieldZoneValue = $mappedDataFieldZone;
        }
        if ($this->showUsage) echo "PivotUtil middle 2.3 process() PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";

        $dataMap = $this->dataMap;
        if (is_array($dataMap)) {
            $dataMap = function ($v) use ($dataMap) {
                return isset($dataMap[$v]) ? $dataMap[$v] : $v;
            };
        }


        $indexToMappedData = [];
        // $indexToMappedData = $indexToData;
        // Util::prettyPrint($indexToData);
        // exit;
        // print_r($cMetas); echo "<br>";

        // foreach ($indexToMappedData as $ri => $cis) {
        // ...
        // }
        if ($isBunTemplate) $indexes = &$rowIndexesBun;
        else $indexes = &$rowIndexes;
        foreach ($indexes as $ri) {
            $cis = $indexToData[$ri];
            foreach ($cis as $ci => $d) {
                if (is_callable($dataMap)) {
                    Util::init($indexToMappedData, [$ri, $ci], array_combine(
                        array_keys($d),
                        array_map($dataMap, $d, array_keys($d))
                    ));
                } else {
                    foreach ($d as $df => $v) {
                        // print_r($indexToMappedData[$ri][$ci][$df]); echo " ** ";
                        Util::init($indexToMappedData, [$ri, $ci, $df], Util::format(
                            $v,
                            Util::get($this->measures, $df, Util::get($cMetas, $df, []))
                        ));
                    }
                }
            }
        }

        if ($this->showUsage) echo "PivotUtil middle 3 process() PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";

        $this->computeDataCellMaps();
        if ($this->showUsage) echo "PivotUtil middle 4 process() PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";

        // list($mappedData, $indexToDataClass, $indexToDataExcelStyle, $indexToDataSpreadsheetStyle) =
        //     $this->getDataAttributes($indexToData, $rowNodesInfo, $colNodesInfo);
        // if (isset($this->map['dataCell'])) $indexToMappedData = $mappedData;

        $waitingFieldsType = array_values($this->waitingFields);
        $dataFieldsType = array_fill(0, count($dataFields), 'data');
        $columnFieldsType = array_fill(0, count($colFields), 'column');
        $rowFieldsType = array_fill(0, count($rowFields), 'row');

        $waitingFieldsSort = array_fill(0, count($this->waitingFields), 'noSort');
        $dataFieldsSort = array_fill(0, count($dataFields), 'noSort');
        $columnFieldsSort = array_fill(0, count($colFields), 'noSort');
        $rowFieldsSort = array_fill(0, count($rowFields), 'noSort');
        $colSortDataField = null;
        foreach ($this->columnSort as $field => $dir) {
            foreach ($dataFields as $i => $dataField) {
                if ($dataField === $field && ($dir === 'asc' || $dir === 'desc')) {
                    $dataFieldsSort[$i] .= ' columnsort' . $dir;
                    $colSortDataField = $field;
                }
            }
        }
        $rowSortDataField = null;
        foreach ($this->rowSort as $field => $dir) {
            foreach ($dataFields as $i => $dataField) {
                if ($dataField === $field && ($dir === 'asc' || $dir === 'desc')) {
                    $dataFieldsSort[$i] .= ' rowsort' . $dir;
                    $rowSortDataField = $field;
                }
            }
        }
        if (!$colSortDataField) {
            foreach ($this->columnSort as $field => $dir) {
                foreach ($colFields as $i => $colField) {
                    if ($colField == $field && ($dir === 'asc' || $dir === 'desc')) {
                        $columnFieldsSort[$i] = 'columnsort' . $dir;
                    }
                }
            }
        }
        if (!$rowSortDataField) {
            foreach ($this->rowSort as $field => $dir) {
                foreach ($rowFields as $i => $rowField) {
                    if ($rowField === $field && ($dir === 'asc' || $dir === 'desc')) {
                        $rowFieldsSort[$i] = 'rowsort' . $dir;
                    }
                }
            }
        }

        if ($this->showUsage) echo "PivotUtil middle 5 process() PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";


        // echo "dataFieldsClass="; print_r($dataFieldsClass); echo "<br>";
        $this->FieldsNodesIndexes = array(
            'waitingFields' => $waitingFields,
            'dataFields' => $dataFields,
            'colFields' => $colFields,
            'rowFields' => $rowFields,
            'waitingFieldsType' => $waitingFieldsType,
            'dataFieldsType' => $dataFieldsType,
            'columnFieldsType' => $columnFieldsType,
            'rowFieldsType' => $rowFieldsType,
            'waitingFieldsSort' => $waitingFieldsSort,
            'dataFieldsSort' => $dataFieldsSort,
            'columnFieldsSort' => $columnFieldsSort,
            'rowFieldsSort' => $rowFieldsSort,

            'mappedDataFields' => $mappedDataFields,
            'mappedDataHeaders' => $mappedDataHeaders,
            'mappedColFields' => $mappedColFields,
            'mappedRowFields' => $mappedRowFields,
            'mappedWaitingFields' => $mappedWaitingFields,

            'mappedDataFieldZoneValue' => $mappedDataFieldZoneValue,

            'colNodes' => $colNodes,
            'rowNodes' => $rowNodes,
            'mappedColNodes' => $mappedColNodes,
            'mappedRowNodes' => $mappedRowNodes,
            'mappedRowNodesBun' => $mappedRowNodesBun,

            'colIndexes' => $colIndexes,
            'rowIndexes' => $rowIndexes,
            'rowIndexesBun' => $rowIndexesBun,
            'colNodesInfo' => $colNodesInfo,
            'rowNodesInfo' => $rowNodesInfo,
            'rowNodesInfoBun' => $rowNodesInfoBun,

            // 'indexToMappedData' => $indexToMappedData,
            // 'indexToData' => $indexToData,
            // 'indexToDataClass' => $indexToDataClass,
            // 'indexToDataExcelStyle' => $indexToDataExcelStyle,
            // 'indexToDataSpreadsheetStyle' => $indexToDataSpreadsheetStyle,

            'rowNodesClass' => $rowNodesClass,
            'rowNodesClassBun' => $rowNodesClassBun,
            'colNodesClass' => $colNodesClass,
            'rowFieldsClass' => $rowFieldsClass,
            'columnFieldsClass' => $colFieldsClass,
            'dataFieldsClass' => $dataFieldsClass,
            'dataHeadersClass' => $dataHeadersClass,
            'waitingFieldsClass' => $waitingFieldsClass,

            'rowNodesExcelStyle' => $rowNodesExcelStyle,
            'rowNodesExcelStyleBun' => $rowNodesExcelStyleBun,
            'colNodesExcelStyle' => $colNodesExcelStyle,
            'rowFieldsExcelStyle' => $rowFieldsExcelStyle,
            'columnFieldsExcelStyle' => $colFieldsExcelStyle,
            'dataFieldsExcelStyle' => $dataFieldsExcelStyle,
            'dataHeadersExcelStyle' => $dataHeadersExcelStyle,
            'waitingFieldsExcelStyle' => $waitingFieldsExcelStyle,

            'rowNodesSpreadsheetStyle' => $rowNodesSpreadsheetStyle,
            'rowNodesSpreadsheetStyleBun' => $rowNodesSpreadsheetStyleBun,
            'colNodesSpreadsheetStyle' => $colNodesSpreadsheetStyle,
            'rowFieldsSpreadsheetStyle' => $rowFieldsSpreadsheetStyle,
            'columnFieldsSpreadsheetStyle' => $colFieldsSpreadsheetStyle,
            'dataFieldsSpreadsheetStyle' => $dataFieldsSpreadsheetStyle,
            'dataHeadersSpreadsheetStyle' => $dataHeadersSpreadsheetStyle,
            'waitingFieldsSpreadsheetStyle' => $waitingFieldsSpreadsheetStyle,

            'numRow' => $numRows,
            'numRows' => $numRows,
        );
        if ($this->showUsage) echo "PivotUtil end process() PHP memory usage =  " . number_format(memory_get_usage()) . "<br>\n";
    }


    public function getFieldsNodesIndexes()
    {
        return $this->FieldsNodesIndexes;
    }
}
