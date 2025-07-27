<?php

namespace koolreport\datagrid;

use \koolreport\core\Widget;
use \koolreport\core\Utility as Util;
use \koolreport\core\DataStore;

class DataTables extends Widget
{
    protected $name;
    protected $columns;
    protected $data;
    protected $options;
    protected $emptyValue;
    protected $showFooter;
    protected $clientEvents;
    protected $trueColKeys = [];

    public function version()
    {
        return "6.0.0";
    }

    protected function resourceSettings()
    {
        $resources = [];
        $themeBase = $this->getThemeBase();
        switch ($themeBase) {
            case "bs4":
                $resources = array(
                    "library" => array("jQuery"),
                    "folder" => "DataTables",
                    "js" => array(
                        "KRDataTables.js",
                        "datatables.min.js",
                        // "dataTables1.10.25.js",
                        array(
                            "pagination/input.js",
                            "datatables.bs4.min.js"
                        )
                    ),
                    "css" => array(
                        "KRDataTables.css",
                        "datatables.bs4.min.css",
                    )
                );
                break;
            case "bs3":
            default:
                $resources = array(
                    "library" => array("jQuery"),
                    "folder" => "DataTables",
                    "js" => array(
                        "KRDataTables.js",
                        "datatables.min.js",
                        // "dataTables1.10.25.js",
                        [
                            "pagination/input.js"
                        ]
                    ),
                    "css" => array(
                        "KRDataTables.css",
                        "datatables.min.css",
                    )
                );
        }

        $pluginNameToFiles = array(
            "AutoFill" => array(
                "AutoFill-2.3.5/js/dataTables.autoFill.min.js"
            ),
            "Buttons" => array(
                "Buttons-1.6.2/js/dataTables.buttons.min.js",
                "Buttons-1.6.2/js/buttons.colVis.min.js",
                "Buttons-1.6.2/js/buttons.html5.min.js",
                "Buttons-1.6.2/js/buttons.print.min.js",
                "JSZip-2.5.0/jszip.min.js",
                "pdfmake-0.1.36/pdfmake.min.js",
                ["pdfmake-0.1.36/vfs_fonts.js"], //vfs_fonts must be loaded after pdfmake.min.js
            ),
            "ColReorder" => array(
                "ColReorder-1.5.2/js/dataTables.colReorder.min.js",
            ),
            "FixedColumns" => array(
                "FixedColumns-3.3.1/js/dataTables.fixedColumns.min.js",
            ),
            "FixedHeader" => array(
                "FixedHeader-3.1.7/js/dataTables.fixedHeader.min.js"
            ),
            "KeyTable" => array(
                "KeyTable-2.5.2/js/dataTables.keyTable.min.js"
            ),
            "Responsive" => array(
                "Responsive-2.2.4/js/dataTables.responsive.min.js"
            ),
            "RowGroup" => array(
                "RowGroup-1.1.2/js/dataTables.rowGroup.min.js"
            ),
            "RowReorder" => array(
                "RowReorder-1.2.7/js/dataTables.rowReorder.min.js"
            ),
            "Scroller" => array(
                "Scroller-2.0.2/js/dataTables.scroller.min.js"
            ),
            "SearchPanes" => array(
                "SearchPanes-1.1.0/js/dataTables.searchPanes.min.js"
            ),
            "Select" => array(
                "Select-1.3.1/js/dataTables.select.min.js"
            ),
        );

        $pluginJs = [];
        foreach ($this->plugins as $name) {
            if (isset($pluginNameToFiles[$name])) {
                foreach ($pluginNameToFiles[$name] as $jsfile) {
                    array_push($pluginJs, $jsfile);
                }
            }
        }
        $resources['js'][2] = array_merge($resources['js'][2], $pluginJs);

        $pluginNameToCsses = array(
            "FixedHeader" => array(
                "FixedHeader-3.1.7/css/fixedHeader.dataTables.min.css",
            ),
        );

        if ($themeBase === "bs4") {
            $pluginNameToCsses["FixedHeader"][] = "FixedHeader-3.1.7/css/fixedHeader.bootstrap4.min.css";
        }

        $pluginCsses = [];
        foreach ($this->plugins as $name) {
            $cssFiles = Util::get($pluginNameToCsses, $name, []);
            $pluginCsses = array_merge($pluginCsses, $cssFiles);
        }
        $resources['css'] = array_merge($resources['css'], $pluginCsses);

        if (!empty($this->clientRowGroup) || !empty($this->rowDetailData)) {
            $resources['library'][] = 'font-awesome';
        }

        return $resources;
    }

    protected function onInit()
    {
        $this->useLanguage();
        $scope = Util::get($this->params, "scope", array());
        $this->scope = is_callable($scope) ? $scope() : $scope;
        $this->name = Util::get($this->params, "name");
        $this->template = Util::get($this->params, 'template', 'DataTables');
        $this->fastRender = Util::get($this->params, 'fastRender', false);
        $this->columns = Util::get($this->params, "columns", array());
        $this->showFooter = Util::get($this->params, "showFooter", false);
        $this->clientEvents = Util::get($this->params, "clientEvents", false);
        $this->serverSide = Util::get($this->params, "serverSide", false);
        $this->method = strtoupper(Util::get($this->params, "method", 'get'));
        $this->submitType = $this->method === 'POST' ? $_POST : $_GET;
        $this->complexHeaders = Util::get($this->params, "complexHeaders", false);
        $this->headerSeparator = Util::get($this->params, "headerSeparator", '-');
        $this->searchOnEnter = Util::get($this->params, "searchOnEnter", false);
        $this->searchMode = strtolower(Util::get($this->params, "searchMode", "default"));
        $this->searchMode = array_flip(explode("|", $this->searchMode));
        $this->emptyValue = Util::get($this->params, "emptyValue", "-");
        $this->cssClass = Util::get($this->params, "cssClass", array());
        $this->attributes = Util::get($this->params, "attributes", array());
        $this->onBeforeInit = Util::get($this->params, "onBeforeInit");
        $this->defaultPlugins = Util::get($this->params, "defaultPlugins", [
            "AutoFill", "ColReorder", "RowGroup", "Select"
        ]);
        $this->plugins = Util::get($this->params, "plugins", []);
        $this->plugins = array_merge($this->plugins, $this->defaultPlugins);
        $this->clientRowGroup = Util::get($this->params, "clientRowGroup", []);
        if (!empty($this->clientRowGroup) && !in_array("RowGroup", $this->plugins)) {
            $this->plugins[] = "RowGroup";
        }
        $this->rowDetailData = Util::get($this->params, "rowDetailData");
        $this->rowDetailIcon = Util::get($this->params, "rowDetailIcon", true);
        $this->rowDetailSelector = Util::get($this->params, "rowDetailSelector", "");

        $this->useDataSource($this->scope);

        if (!$this->name) {
            $this->name = "datatables" . Util::getUniqueId();
        }

        if ($this->dataStore == null) {
            throw new \Exception("dataSource is required for DataTables");
            return;
        }


        $this->options = array(
            "searching" => false,
            "paging" => false,
        );


        if ($this->languageMap != null) {
            $this->options["language"] = $this->languageMap;
        }

        $this->options = array_merge(
            $this->options,
            Util::get($this->params, "options", array())
        );
    }

    protected function formatValue($value, $format, $row = null, $cKey = null)
    {
        $formatValue = Util::get($format, "formatValue", null);

        if (is_string($formatValue)) {
            eval('$fv="' . str_replace('@value', '$value', $formatValue) . '";');
            return $fv;
        } else if (is_callable($formatValue)) {
            return $formatValue($value, $row, $cKey);
        } else {
            return Util::format($value, $format);
        }
    }

    protected function buildServerSide()
    {
        $columnsData = [];
        foreach ($this->showColumnKeys as $colKey)
            $columnsData[] = ['data' => $colKey];
        $scopeJson = json_encode($this->scope);
        $this->options = array_merge($this->options, [
            'serverSide' => true,
            'ajax' => [
                'url' => '',
                'data' => "function(d) {
                    d.id = '{$this->name}';
                    var scope = {$scopeJson};
					for (var p in scope)
						if (scope.hasOwnProperty(p))
							d[p] = scope[p];
                }",
                'type' => "{$this->method}",
                'dataFilter' => "function(data) {
                    var markStart = \"<dt-ajax id='dt_$this->name'>\";
                    var markEnd = '</dt-ajax>';
                    var start = data.indexOf(markStart);
                    var end = data.indexOf(markEnd);
                    var s = data.substring(start + markStart.length, end);
                    return s;
                }",
            ],
            "columns" => $columnsData
        ]);
    }

    protected function buildComplexHeaders()
    {
        $showColumnKeys = $this->showColumnKeys;
        $sep = $this->headerSeparator;
        $headerRows = [];
        //Create empty header rows array
        foreach ($showColumnKeys as $cKey) {
            $cKey = explode($sep, $cKey);
            if (count($headerRows) < count($cKey)) {
                $aHeaderRow = [];
                array_push($headerRows, $aHeaderRow);
            }
        }
        $numRow = count($headerRows);
        //Fill in header row values from column names
        foreach ($showColumnKeys as $cKey) {
            $cKey = explode($sep, $cKey);
            for ($i = 0; $i < $numRow; $i++) {
                $header = Util::get($cKey, $i, null);
                array_push($headerRows[$i], [
                    'text' => $header
                ]);
            }
        }
        $lastSameHeaderIndexes = [];
        $lastNotNullHeaderIndexes = [];
        // Util::prettyPrint($headerRows); 
        $newHeaderRows = $headerRows;
        foreach ($headerRows as $rowIndex => $aHeaderRow) {
            foreach ($aHeaderRow as $colIndex => $header) {
                $currentText = $header['text'];
                if (!isset($currentText)) {
                    if ($rowIndex === 0) continue;
                    $lastNotNull = $lastNotNullHeaderIndexes[$colIndex];
                    $rowspan = (int) Util::init(
                        $newHeaderRows,
                        [$lastNotNull, $colIndex, 'rowspan'],
                        1
                    );
                    $newHeaderRows[$lastNotNull][$colIndex]['rowspan'] = $rowspan + 1;
                } else {
                    $lastNotNullHeaderIndexes[$colIndex] = $rowIndex;
                    if (!isset($lastSameHeaderIndexes[$rowIndex])) {
                        $lastSameHeaderIndexes[$rowIndex] = $colIndex;
                        continue;
                    };
                    $lastIndex = $lastSameHeaderIndexes[$rowIndex];
                    $lastSameHeader = $aHeaderRow[$lastIndex]['text'];

                    // echo "$currentText - $lastSameHeader <br>";
                    $isEqual = ($currentText === $lastSameHeader);
                    $isSameParents = true;
                    for ($k = $rowIndex - 1; $k > -1; $k--) {
                        $currentParent = $headerRows[$k][$colIndex]['text'];
                        $lastParent = $headerRows[$k][$lastIndex]['text'];
                        // echo "$currentParent - $lastParent <br>";
                        if ($currentParent !== $lastParent) {
                            $isSameParents = false;
                            break;
                        }
                    }
                    // echo "isEqual = $isEqual<br>";
                    // echo "isSameParents = $isSameParents<br>";

                    if (!$isEqual || !$isSameParents) {
                        $lastSameHeaderIndexes[$rowIndex] = $colIndex;
                    } else if ($colIndex < count($aHeaderRow) - 1) {
                        // echo "is equal and same parent <br>";
                        $colspan = (int) Util::init(
                            $newHeaderRows,
                            [$rowIndex, $lastIndex, 'colspan'],
                            1
                        );
                        // echo "i=$rowIndex lastIndex=$lastIndex colspan=$colspan<br>";
                        $newHeaderRows[$rowIndex][$lastIndex]['colspan'] = $colspan + 1;
                        $newHeaderRows[$rowIndex][$colIndex]['text'] = null;
                    } else {
                        $colspan = (int) Util::init(
                            $newHeaderRows,
                            [$rowIndex, $lastIndex, 'colspan'],
                            1
                        );
                        $newHeaderRows[$rowIndex][$lastIndex]['colspan'] = $colspan + 1;
                        $newHeaderRows[$rowIndex][$colIndex]['text'] = null;
                    }
                }
            }
        }
        // Util::prettyPrint($headerRows);  
        return $newHeaderRows;
    }

    protected function buildClientRowGroup()
    {
        $showColumnKeys = $this->showColumnKeys;
        $showColumnKeys = array_flip($showColumnKeys);
        $orderOption = [];
        $rowGroupOption = [
            'dataSrc' => []
        ];
        $startRender = $endRender = "var startRenderLevels = endRenderLevels = {}; ";
        $expandIcon = Util::get($this->params, 'expandIcon', "<i class=\'far fa-plus-square\' aria-hidden=\'true\'></i>");
        $collapseIcon = Util::get($this->params, 'collapseIcon', "<i class=\'far fa-minus-square\' aria-hidden=\'true\'></i>");
        $grLevel = 0;
        $grCols = [];
        foreach ($this->clientRowGroup as $grCol => $grOption) {
            $grCols[$grLevel] = $grCol;
            $colIndex = $showColumnKeys[$grCol];
            $dir = Util::get($grOption, 'direction', 'asc');
            $orderOption[] = [$colIndex, $dir];
            $rowGroupOption['dataSrc'][] = $colIndex;

            //Build agg values
            $calculate = Util::get($grOption, 'calculate', []);
            $aggValues = [];
            foreach ($calculate as $aggName => $aggConfig) {
                $aggOp = Util::get($aggConfig, 0, Util::get($aggConfig, 'operator'));
                $aggFunc = Util::get($aggConfig, 'aggregate');
                $aggField = Util::get($aggConfig, 1, Util::get($aggConfig, 'field'));
                $aggColIndex = $showColumnKeys[$aggField];
                if ($aggFunc) {
                    $aggVarName = "{$aggField}AggValue{$grLevel}" . md5($aggFunc);
                    $aggValue = "
                    var $aggVarName = $aggFunc(rows, group, $aggColIndex);
                    ";
                } else {
                    $aggVarName = "{$aggField}AggValue{$grLevel}" . md5($aggOp);
                    switch ($aggOp) {
                        case "avg":
                        case "sum":
                            $reduceFunc = "return a + 1*b.replace(/[^\d\.\-]/g, '');";
                            $initValue = 0;
                            break;
                        case "count":
                            $reduceFunc = "return a + 1;";
                            $initValue = 0;
                            break;
                        case "min":
                            $reduceFunc = "return a < b ? a : b;";
                            $initValue = "Number.MAX_VALUE";
                            break;
                        case "max":
                            $reduceFunc = "return a > b ? a : b;";
                            $initValue = "- Number.MAX_VALUE";
                            break;
                    }
                    $aggValue = "
                        var $aggVarName = rows
                            .data()
                            .pluck($aggColIndex)
                            // .pluck('$aggField')
                            .reduce( function (a, b) {
                                $reduceFunc
                            }, $initValue);
                    ";
                    if ($aggOp === 'avg') $aggValue .= " / rows.count()";
                }

                $formatFunc = Util::get($aggConfig, 'format');
                if ($formatFunc) {
                    $aggValue .= "$aggVarName = $formatFunc($aggVarName);";
                }

                $aggValues[$aggName] = [
                    'name' => $aggVarName,
                    'value' => $aggValue
                ];
            }

            //Replace agg place holders
            $top = addslashes(Util::get($grOption, "top", ""));
            $bottom = addslashes(Util::get($grOption, "bottom", ""));
            foreach ($calculate as $aggName => $aggConfig) {
                $aggValue = $aggValues[$aggName]['value'];
                $startRender .= " $aggValue ";
                $endRender .= " $aggValue ";
                $aggVarName = $aggValues[$aggName]['name'];
                $top = str_replace("{{$aggName}}", "' + $aggVarName + '", $top);
                $bottom = str_replace("{{$aggName}}", "' + $aggVarName + '", $bottom);
            }
            $onclick = "onclick=\'KR{$this->name}.expandCollapse(this);\'";
            $top = str_replace("{expandCollapseIcon}", "<span class=\'group-expand\' style=\'display:none;\' $onclick >$expandIcon</span><span class=\'group-collapse\' $onclick >$collapseIcon</span>", $top);
            $replaceTop = "
                var top = '$top';
            ";
            for ($i = 0; $i <= $grLevel; $i++) {
                $replaceTop .= "
                    top = top.replace(/{{$grCols[$i]}}/g, window['dtRowGroups'][{$i}]);
                ";
            }
            $startRender .= "
                $replaceTop
                startRenderLevels[{$grLevel}] = $('<tr/>')
                    .append( top )
                ;
            ";

            $bottom = str_replace("{expandCollapseIcon}", "<span class=\'group-expand\' style=\'display:none;\' $onclick >$expandIcon</span><span class=\'group-collapse\' $onclick >$collapseIcon</span>", $bottom);
            $replaceBottom = "
                var bottom = '$bottom';
            ";
            for ($i = 0; $i <= $grLevel; $i++) {
                $replaceBottom .= "
                    bottom = bottom.replace(/{{$grCols[$i]}}/g, window['dtRowGroups'][{$i}]);
                ";
            }
            $endRender .= "
                $replaceBottom
                endRenderLevels[{$grLevel}] = $('<tr/>')
                    .append( bottom )
                ;
            ";

            $grLevel++;
        }

        $startRenderFunc = "
            function ( rows, group, level ) {
                // console.log('start render group row');
                // console.log(rows, group, level);
                window['dtRowGroups'] = window['dtRowGroups'] || {};
                window['dtRowGroups'][level] = group;
                {$startRender}
                return startRenderLevels[level];
            }
        ";
        $rowGroupOption['startRender'] = $startRenderFunc;
        $endRenderFunc = "
            function ( rows, group, level ) {
                // console.log(rows, group, level);
                window['dtRowGroups'] = window['dtRowGroups'] || {};
                window['dtRowGroups'][level] = group;
                {$endRender}
                return endRenderLevels[level];
            }
        ";
        $rowGroupOption['endRender'] = $endRenderFunc;

        $this->options['order'] = $orderOption;
        $this->options['rowGroup'] = $rowGroupOption;
    }

    protected function buildColumnDefs()
    {
        $columnDefs = Util::init($this->options, "columnDefs", []);
        $defs = [
            'width', 'visible', 'createdCell', 'render', 'searchable',
            'type', 'title', 'orderData', 'orderDataType', 'orderable', 'name',
            'defaultContent', 'data', 'contentPadding', 'className', 'cellType'
        ];
        foreach ($this->showColumnKeys as $i => $cKey) {
            $cMeta = Util::get($this->cMetas, $cKey, []);
            foreach ($defs as $def) {
                if (isset($cMeta[$def])) {
                    $columnDef = [
                        'targets' => $i,
                        $def => $cMeta[$def]
                    ];
                    $columnDefs[] = $columnDef;
                }
            }
        }
        $this->options["columnDefs"] = $columnDefs;
    }

    protected function buildFastRender()
    {
        Util::init($this->options, 'deferRender', true);
        Util::init($this->options, 'columns', []);
        $trClass = Util::get($this->cssClass, "tr");
        $tdClass = Util::get($this->cssClass, "td");
        $getMappedProperty = function ($mappedProperty, $default) {
            $args = func_get_args();
            $args = array_slice($args, 2);
            $property = is_callable($mappedProperty) ?
                call_user_func_array($mappedProperty, $args) : $mappedProperty;
            if (!isset($property)) $property = $default;
            return $property;
        };

        foreach ($this->showColumnKeys as $ci => $cKey) {
            $cMeta = Util::get($this->cMetas, $cKey, []);
            $colOption = [
                // 'title' => Util::get($cMetas,[$cKey, "label"], $cKey),
                'data' => $ci,
                // 'name' => $cKey,
                "className" => $getMappedProperty($tdClass, "", [], $cKey, $cMeta),
            ];
            $this->options['columns'][] = $colOption;
        }
        $rowClasses = $getMappedProperty($trClass, "", [], $cMeta);
        Util::init($this->options, 'createdRow', "function( row, data, dataIndex ) {
            $(row).addClass( '$rowClasses' );
        }");
    }

    protected function buildRowDetailData()
    {
        $rowDetailData = $this->rowDetailData;
        if (is_callable($rowDetailData)) {
            foreach ($this->dataRows as $i => $row) {
                // $row = array_combine($this->showColumnKeys, $row);
                $this->dataRows[$i]['{rowDetailData}'] =
                    $rowDetailData($this->dataStore->get($i));
            }
            $this->options['rowDetailData'] = "function(row) { return row['{rowDetailData}']; }";
        } else if (is_string($rowDetailData)) {
            $this->options['rowDetailData'] = $rowDetailData;
        }

        $columnDefs = Util::init($this->options, "columnDefs", []);
        if ($this->fastRender) {
            array_unshift($this->options['columns'], [
                "data" => 'rowDetailIcon',
            ]);
        } else {
            if (is_callable($rowDetailData)) {
                //Add invisible {rowDetailData} column
                array_push($this->showColumnKeys, '{rowDetailData}');
                $rowDetailDataOrder =  count($this->showColumnKeys);
                $columnDefs[] = [
                    'targets' => $rowDetailDataOrder,
                    "visible" => false,
                ];
                $this->options['rowDetailData'] = "function(row) { return row[$rowDetailDataOrder]; }";
            }
        }
        $columnDefs[] = [
            'targets' => 0,
            "title" => "",
            "className" => 'details-control',
            "orderable" => false,
            "width" => "1px",
            "visible" => $this->rowDetailIcon,
        ];
        $this->options["columnDefs"] = $columnDefs;

        array_unshift($this->showColumnKeys, 'rowDetailIcon');
        foreach ($this->dataRows as $i => $row) {
            $this->dataRows[$i]['rowDetailIcon'] =  "<i class='far fa-plus-square' aria-hidden='true'></i>";
        }
    }

    protected function buildDataRows($rowType = 'assoc')
    {
        $this->dataRows = [];
        $this->dataStore->popStart();
        while ($row = $this->dataStore->pop()) {
            $dataRow = $row;
            foreach ($this->showColumnKeys as $ci => $cKey) {
                $cMeta = Util::get($this->cMetas, $cKey, []);
                $formatValue = Util::get($cMeta, "formatValue", null);
                $key = ($rowType === 'assoc') ? $cKey : $ci;
                if (isset($row[$cKey]) || is_callable($formatValue)) {
                    $value = ($cKey !== "#") ?
                        Util::get($row, $cKey, $this->emptyValue)
                        : ($ci + $cMeta["start"]);
                    ob_start();
                    echo $this->formatValue($value, $cMeta, $row, $cKey);
                    $dataRow[$key] = ob_get_clean();
                } else {
                    $dataRow[$key] = $this->emptyValue;
                }
            }
            $this->dataRows[] = $dataRow;
        }
    }

    protected function onRender()
    {
        $meta = $this->dataStore->meta();
        $cMetas = Util::init($meta, 'columns', []);


        $showColumnKeys = array();
        if ($this->columns == array()) {
            $this->dataStore->popStart();
            $row = $this->dataStore->pop();
            if ($row) {
                $showColumnKeys = array_keys($row);
            } else {
                $showColumnKeys = array_keys($cMetas);
            }
        } else {
            foreach ($this->columns as $cKey => $cValue) {
                if (gettype($cValue) == "array") {
                    if ($cKey === "#") {
                        $cMetas[$cKey] = array(
                            "type" => "number",
                            "label" => "#",
                            "start" => 1,
                        );
                    }
                    if (!isset($cMetas[$cKey])) $cMetas[$cKey] = [];
                    $cMetas[$cKey] =  array_merge($cMetas[$cKey], $cValue);
                    if (!in_array($cKey, $showColumnKeys)) {
                        array_push($showColumnKeys, $cKey);
                    }
                } else {
                    if ($cValue === "#") {
                        $cMetas[$cValue] = array(
                            "type" => "number",
                            "label" => "#",
                            "start" => 1,
                        );
                    }
                    if (!in_array($cValue, $showColumnKeys)) {
                        array_push($showColumnKeys, $cValue);
                    }
                }
            }
        }
        $this->showColumnKeys = $showColumnKeys;
        $meta["columns"] = $this->cMetas = $cMetas;


        if ($this->serverSide) $this->buildServerSide();

        $headerRows = $this->complexHeaders ? $this->buildComplexHeaders() : [];

        if (!empty($this->clientRowGroup)) $this->buildClientRowGroup();

        if ($this->fastRender) {
            $this->buildDataRows('array');
            $this->buildFastRender();
        } else {
            $this->buildDataRows('assoc');
        }

        if ($this->rowDetailData) $this->buildRowDetailData();

        $this->buildColumnDefs();

        $this->template($this->template, array(
            "uniqueId" => $this->name,
            "showColumnKeys" => $this->showColumnKeys,
            "headerRows" => $headerRows,
            "meta" => $meta,
        ));
    }

    protected function onFurtherProcessRequest($node)
    {
        if (!$this->serverSide) {
            return $node;
        }
        function getFinalSources($node)
        {
            $finalSources = [];
            $sources = [];
            $index = 0;
            while ($source = $node->previous($index)) {
                $sources[] = $source;
                $index++;
            }
            if (empty($sources)) {
                return [$node];
            }
            foreach ($sources as $source) {
                $finalSources = array_merge(
                    $finalSources,
                    getFinalSources($source)
                );
            }
            return $finalSources;
        }
        function setEnded($node, $bool)
        {
            $node->setEnded($bool);
            $index = 0;
            while ($source = $node->previous($index)) {
                setEnded($source, $bool);
                $index++;
            }
        }
        // $queryParams = $this->parseRequest($this->name, $this->method);
        $finalSources = getFinalSources($node);

        if (empty($this->columns)) {
            $queryParams = [
                'start' => 0,
                'length' => 1
            ];
            $dataStore = new \koolreport\core\DataStore();
            foreach ($finalSources as $finalSource) {
                if (method_exists($finalSource, 'queryProcessing')) {
                    $finalSource->queryProcessing($queryParams);
                    $dataStore = $node->pipe($dataStore);
                    $dataStore->requestDataSending();
                    setEnded($node, false);
                }
            }
            // $dataStore->popStart();
            // $row = $dataStore->pop();
            // if($row) {
            //     $this->trueColKeys = array_keys($row);
            // } else {
            // 	$this->trueColKeys = [];
            // }
            $this->trueColKeys = array_keys($dataStore->meta()["columns"]);
        } else {
            $this->trueColKeys = array_keys($this->columns);
        }

        foreach ($finalSources as $finalSource) {
            if (!method_exists($finalSource, 'queryProcessing')) continue;
            $queryParams = $this->parseRequest($finalSource, $this->name, $this->method);
            $originalQuery = $finalSource->originalQuery;
            $searchQuery = Util::get($this->params, "searchQuery", $originalQuery);
            if (stripos($searchQuery, "{datatables_search}") !== false) {
                $occurenceCount = 0;
                $allSearchParams = [];
                $searchParams = Util::get($queryParams, "searchParams", []);
                // echo "searchParams="; print_r($searchParams); echo "<br>";
                while (stripos($searchQuery, "{datatables_search}") !== false) {
                    $searchSql = Util::get($queryParams, "search", "1=1");
                    foreach ($searchParams as $param => $value) {
                        $searchSql = str_ireplace($param, $param . "_" . $occurenceCount, $searchSql);
                        $allSearchParams[$param . "_" . $occurenceCount] = $value;
                    }
                    // echo "searchSql=$searchSql<br>";
                    $searchQuery = $this->replaceFirstOccurence($searchQuery, "{datatables_search}", $searchSql);
                    $occurenceCount++;
                }
                // echo "searchQuery=$searchQuery<br>";
                $queryParams["search"] = null;
                $queryParams["searchParams"] = [];
                $sqlParams = $finalSource->getSqlParams();
                $sqlParams = array_merge($sqlParams, $allSearchParams);
                $query = $finalSource->getQuery();
                $finalSource->query($query, $sqlParams);
                $finalSource->originalQuery = $searchQuery;
            }
            $finalSource->queryProcessing($queryParams);
        }
        return $node;
    }

    protected function replaceFirstOccurence($haystack, $needle, $replace)
    {
        $pos = stripos($haystack, $needle);
        if ($pos !== false) {
            $newstring = substr_replace($haystack, $replace, $pos, strlen($needle));
        }
        return $newstring;
    }

    protected function getSearchAllSql(
        $datasource,
        $columns,
        $searchAllString,
        &$queryParams
    ) {
        $searchMode = $this->searchMode;
        $strToSql = function ($datasource, $columns, $searchStr, $searchOrder = 0, $searchAndOrder = 0)
        use (&$queryParams, $searchMode) {
            $trueColKeys = $this->trueColKeys;
            $phrases = [];
            $searchStr = preg_replace_callback('/"([^"]*)"/', function ($matches)
            use (&$phrases) {
                if (!empty($matches[1])) {
                    array_push($phrases, $matches[1]);
                }
                return "";
            }, $searchStr);
            $searchStr = preg_replace_callback('/([^\s\t]*)/', function ($matches)
            use (&$phrases) {
                if (!empty($matches[1])) {
                    array_push($phrases, $matches[1]);
                }
                return "";
            }, $searchStr);
            $sql = "";
            foreach ($phrases as $i => $phrase) {
                $phraseSql = "";
                foreach ($columns as $col) {
                    $searchable = Util::get($col, 'searchable', true);
                    if ($searchable !== "true") continue;
                    $colKey = $col['data'];
                    if (!in_array($colKey, $trueColKeys)) continue;
                    $paramName = ":{$colKey}_search_all_{$searchOrder}_{$searchAndOrder}_$i";
                    $this->addSqlCondition($phraseSql, "OR", "{$colKey} like $paramName");
                    $phrase = isset($searchMode["exact"]) ? $phrase : "%{$phrase}%";
                    $queryParams['searchParams'][$paramName] = $phrase;
                }
                $this->addSqlCondition($sql, "AND", $phraseSql);
            }
            return $sql;
        };

        if (isset($searchMode["or"])) {
            $searchAllString = preg_replace('/^\s*or\s+/i', '', $searchAllString);
            $searchAllString = preg_replace('/\s+or\s*$/i', '', $searchAllString);
            $searchAllString = preg_replace('/^\s*or\s+/i', '', $searchAllString);
            $searchStrings = preg_split('/\sor\s/i', $searchAllString);
            $searchAllSql = "";
            foreach ($searchStrings as $searchOrder => $searchStr) {
                if (isset($searchMode["and"])) {
                    $searchAndStrs = $searchStr;
                    // echo "searchAndStrs=$searchAndStrs<br>";
                    $searchAndStrs = preg_replace('/^\s*and\s+/i', '', $searchAndStrs);
                    $searchAndStrs = preg_replace('/\s+and\s*$/i', '', $searchAndStrs);
                    $searchAndStrs = preg_replace('/^\s*and\s+/i', '', $searchAndStrs);
                    $searchAndStrs = preg_split('/\sand\s/i', $searchAndStrs);
                    // echo "searchAndStrs="; print_r($searchAndStrs); echo "<br>";
                    $searchAllAndSql = "";
                    foreach ($searchAndStrs as $searchAndOrder => $searchAndStr) {
                        $searchAndSql = $strToSql(
                            $datasource,
                            $columns,
                            $searchAndStr,
                            $searchOrder,
                            $searchAndOrder
                        );
                        $this->addSqlCondition($searchAllAndSql, "AND", $searchAndSql);
                    }
                    // echo "searchAllAndSql=$searchAllAndSql<br>";
                    $this->addSqlCondition($searchAllSql, "OR", $searchAllAndSql);
                } else {
                    $searchSql = $strToSql($datasource, $columns, $searchStr, $searchOrder);
                    $this->addSqlCondition($searchAllSql, "OR", $searchSql);
                }
            }
        }

        if (!isset($searchMode["or"]) && !isset($searchMode["and"])) {
            $searchAllSql = $strToSql($datasource, $columns, $searchAllString, 0);
        }
        // echo "searchAllSql=$searchAllSql<br>";
        // echo "queryParams="; print_r($queryParams); echo "<br>";
        return $searchAllSql;
    }

    protected function addSqlCondition(&$sqlCondition, $andOr, $addedCondition)
    {
        if (!empty(trim($addedCondition))) {
            if (stripos($addedCondition, " OR ") !== false && $andOr !== "OR")
                $addedCondition = "($addedCondition)";
            $sqlCondition .= (empty($sqlCondition) ? "" : " $andOr ") . $addedCondition;
        }
    }

    protected function parseRequest($datasource, $dtId, $method = 'get')
    {
        $trueColKeys = $this->trueColKeys;
        $queryParams = [
            'start' => 0,
            'length' => 1,
            'searchParams' => [],
        ];
        $request = strtolower($method) === 'post' ? $_POST : $_GET;
        $id = Util::get($request, 'id', null);
        if ($id == $dtId) {
            $searchSql = "";
            $columns = Util::get($request, 'columns', []);
            $searchColsSql = "";
            foreach ($columns as $col) {
                // echo "col="; print_r($col);
                $searchable = Util::get($col, 'searchable', true);
                if ($searchable !== "true") continue;
                $colKey = $col['data'];
                if (!in_array($colKey, $trueColKeys)) continue;
                $colSearchVal = Util::get($col, ['search', 'value'], null);
                if (empty($colSearchVal)) continue;
                $paramName = ":{$colKey}_search";
                $this->addSqlCondition($searchColsSql, "AND", "$colKey like $paramName");
                $searchColPhrase = isset($this->searchMode["exact"]) ?
                    $colSearchVal : "%{$colSearchVal}%";
                $queryParams['searchParams'][$paramName] = $searchColPhrase;
            }
            $this->addSqlCondition($searchSql, "AND", $searchColsSql);
            // echo "searchSql=$searchSql<br>";

            $searchAll = Util::get($request, 'search', []);
            $searchAllString = Util::get($searchAll, 'value', null);
            $searchAllSql = $this->getSearchAllSql(
                $datasource,
                $columns,
                $searchAllString,
                $queryParams
            );
            // echo "searchAllSql=$searchAllSql<br>";

            $this->addSqlCondition($searchSql, "AND", $searchAllSql);
            $queryParams['search'] = $searchSql;
            // echo "searchSql=$searchSql<br>";

            $orders = Util::get($request, 'order', []);
            $orderSql = "";
            foreach ($orders as $order) {
                $orderable = Util::get($col, 'orderable', true);
                if ($orderable !== "true") continue;
                $colKey = $columns[$order['column']]['data'];
                if (!in_array($colKey, $trueColKeys)) continue;
                $dir = strtolower($order['dir']);
                if ($dir !== "asc"  && $dir !== "desc") continue;
                $orderSql .= $colKey . " " . $dir . ",";
            }
            if (!empty($orderSql)) {
                $orderSql = substr($orderSql, 0, -1);
                $queryParams['order'] = $orderSql;
            }

            $start = (int) Util::get($request, 'start', 0);
            $length = (int) Util::get($request, 'length', 1);
            $queryParams['start'] = $start;
            $queryParams['length'] = $length;

            $queryParams['countTotal'] = true;
            $queryParams['countFilter'] = true;
        }
        // echo 'parseRequest queryParams='; print_r($queryParams); 
        return $queryParams;
    }

    /**
     * Render javascript code to implement user's custom script 
     * just before init DataTables
     * 
     * @return null
     */
    protected function clientSideBeforeInit()
    {
        if ($this->onBeforeInit != null) {
            echo "(" . $this->onBeforeInit . ")();";
        }
    }
}
