<?php

namespace koolreport\pivot\processes;

use \koolreport\core\Utility as Util;

class PivotSQL extends Pivot
{
    protected $fieldDelimiter;

    public function onInit()
    {
        if (!isset($this->params["dimensions"])) {
            $columns = Util::get($this->params, "column", array());
            $rows = Util::get($this->params, "row", array());
            $this->params["dimensions"] = ['column' => $columns, 'row' => $rows];
        }
        parent::OnInit();
    }

    public function pipe($node)
    {
        array_push($this->destinations, $node);
        $node->source($this);

        //Get data sources at the beginning of this process' pipe
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
        $this->finalSources = getFinalSources($this);
        foreach ($this->finalSources as $finalSource) {
            $this->finalSource = $finalSource;
            $this->viewQuery = $finalSource->getQuery();
            $this->viewParams = $finalSource->getSqlParams();
            $finalSource->query('select 0');
        }

        return $node;
    }

    protected function onInput($data)
    {
        //Don't next any row with this process
    }

    protected function onInputEnd()
    {
        // echo "onInputEnd expandTrees= "; var_dump($this->expandTrees); echo "<br>";
        $finalSource = $this->finalSource;
        $this->finalSource->query($this->viewQuery, $this->viewParams);

        $this->fieldDelimiter = Util::get($this->params, "fieldDelimiter", ' -||- ');

        $this->rowFields = Util::get($this->dimensions, 'row', []);
        $this->columnFields = Util::get($this->dimensions, 'column', []);

        // echo "this->pivotId=" . $this->pivotId . "<br>";
        $selectMeasureFields = [];
        foreach ($this->aggregates as $field => $aggs) {
            foreach ($aggs as $agg)
                $selectMeasureFields[] = "$agg($field) as '{$agg}({$field})'";
        }
        $selectMeasureFieldsStr = implode(", ", $selectMeasureFields);
        $this->pivotSign = md5(
            $this->pivotId
            . $this->viewQuery 
            . json_encode($this->viewParams)
            . "row" . implode("", $this->rowFields)
            . "column" . implode("", $this->columnFields)
            . $selectMeasureFieldsStr
        );
        // echo "pivotSign = " . $this->pivotSign . '<br>';


        // $expandTree = Util::get($_SESSION, 'expandTree', []);
        // echo "expandTree="; Util::prettyPrint($expandTree);
        $expandTrees = $this->expandTrees;
        $dsData = [];

        if (! $this->isUpdate) {
            //If this is an inital page load, clear session data of this pivotsql
            //Basically, a pivot data session is only alive within xhr calls
            unset($_SESSION['pivotSQLData'][$this->pivotSign]);
        }

        //If it is NOT an xhr request OR there's NO session data
        //then create an initial expand tree
        if (! $this->isUpdate || ! isset($_SESSION['pivotSQLData'][$this->pivotSign])) {
            $this->rowRoot = $this->columnRoot = [
                "name" => "root",
                "expanded" => true,
                "visible" => true,
                "parent" => null,
                "level" => -2,
                "children" => []
            ];
            $this->rowRoot['dimension'] = 'row';
            $this->columnRoot['dimension'] = 'column';
    
            $nullRowNode = $nullColumnNode = [
                "name" => null,
                "expanded" => true,
                "visible" => true,
                "parent" => null,
                "level" => -1,
                "children" => []
            ];
            $nullRowNode['parent'] = $this->rowRoot;
            $nullColumnNode['parent'] = $this->columnRoot;
    
            $this->rowRoot['children'][] = $nullRowNode;
            $this->columnRoot['children'][] = $nullColumnNode;

            $this->expandTrees = [
                'row' => array(
                    'name' => 'root',
                    'children' => array(),
                ),
                'column' => array(
                    'name' => 'root',
                    'children' => array(),
                ),
            ];
            $queries = [];
            $queries = array_merge($this->nodeAndTreeToQueries('column', $this->columnRoot, $this->rowRoot), $queries);
            $queries = array_merge($this->nodeAndTreeToQueries('column', $nullColumnNode, $this->rowRoot), $queries);
            // $queries = array_merge($this->nodeAndTreeToQueries('row', $this->rowRoot, $this->columnRoot), $queries);
            // $queries = array_merge($this->nodeAndTreeToQueries('row', $nullRowNode, $this->columnRoot), $queries);

            Util::init($_SESSION, ['pivotSQLData', $this->pivotSign], []);
        } else {
            // echo "not empty expandTrees<br>";
            $this->stateTrees = $this->expandTreesToStateTrees($expandTrees);
            $this->stateTrees['column']['level'] = $this->stateTrees['row']['level'] = -2;
            $this->fillTree($this->stateTrees['row']);
            $this->fillTree($this->stateTrees['column']);
            $this->rowExpandNowNodes = $this->treeToExpandNowNodes($this->stateTrees['row']);
            $this->columnExpandNowNodes = $this->treeToExpandNowNodes($this->stateTrees['column']);
            
            // echo "<br>stateTrees = "; var_dump($this->stateTrees); 
            // echo "<br>rowExpandNowNodes = "; var_dump($this->rowExpandNowNodes);
            // echo "<br>columnExpandNowNodes = "; var_dump($this->columnExpandNowNodes);
            $this->rowFields = $this->dimensions['row'];
            $this->columnFields = $this->dimensions['column'];
            $queries = [];
            foreach ($this->rowExpandNowNodes as $rowExpandNowNode) {
                $queries = array_merge($this->nodeAndTreeToQueries('row', $rowExpandNowNode, $this->stateTrees['column']), $queries);
            }
            foreach ($this->columnExpandNowNodes as $columnExpandNowNode) {
                $queries = array_merge($this->nodeAndTreeToQueries('column', $columnExpandNowNode, $this->stateTrees['row']), $queries);
            }

            $dsData = Util::get($_SESSION, ['pivotSQLData', $this->pivotSign], []);
        }
        $this->queries = $queries;
        // echo "this->queries="; Util::prettyPrint($this->queries);

        $separator = $this->fieldDelimiter;
        $pivot = $this;
        $setDsData = function(&$dsData, $row) use ($pivot) {
            $separator = $pivot->fieldDelimiter;
            $rowFields = $pivot->rowFields;
            $columnFields = $pivot->columnFields;
            $rowName = $colName = [];
            foreach ($rowFields as $rowField) {
                $rowName[] = isset($row[$rowField]) ? 
                    $row[$rowField] : '{{all}}';
            }
            foreach ($columnFields as $columnField) {
                $colName[] = isset($row[$columnField]) ? 
                    $row[$columnField] : '{{all}}';
            }
            $rowName = implode($separator, $rowName);
            Util::init($dsData, $rowName, ['label' => $rowName]);
            $colName = implode($separator, $colName);
            if (empty($colName)) $colName = "{{all}}";
            foreach ($pivot->aggregates as $field => $aggs) {
                foreach ($aggs as $agg) {
                    //Set {{all}} column at first position
                    $measureColName = "{{all}}{$separator}{$field} - {$agg}";
                    Util::init($dsData, [$rowName, $measureColName], null);

                    $measureColName = "{$colName}{$separator}{$field} - {$agg}";
                    $dsData[$rowName][$measureColName] = $row["$agg($field)"];
                }
            }
        };

        foreach ($queries as $query) {
            // echo '<br>sql query = "' . $query['query'] . '"<br>';
            $result = $finalSource->fetchData($query['query']);

            foreach ($result['data'] as $row) {
                // echo "row="; print_r($row); echo "<br>";
                $setDsData($dsData, $row);
            }
        }

        $_SESSION['pivotSQLData'][$this->pivotSign] = $dsData;

        $cMetas = ['label' => ['type' => 'string']];
        $colKeys = empty($dsData) ? [] : array_keys(array_values($dsData)[0]);
        foreach ($colKeys as $colKey) {
            if ($colKey === 'label') continue;
            $cMetas[$colKey] = ['type' => 'number'];
        }
        foreach ($this->aggregates as $af => $operators) {
            foreach ($operators as $op) {
                $aggField = $af . ' - ' . $op;
                if ($op === 'count percent' || $op === 'sum percent') {
                    $cMetas[$aggField] = [
                        'type' => 'number',
                        'decimals' => 2,
                        'suffix' => '%',
                    ];
                } else {
                    $cMetas[$aggField] = [
                        'type' => 'number',
                    ];
                }
            }
        }

        if (empty($this->expandTrees)) {
            $this->expandTrees = [
                'row' => [
                    'name' => 'root',
                    'children' => []
                ],
                'column' => [
                    'name' => 'root',
                    'children' => []
                ],
            ];
        }
        $this->removeExpandNow($this->expandTrees['row']);
        $this->removeExpandNow($this->expandTrees['column']);
        $this->sendMeta([
            'pivotId' => $this->pivotId,
            'pivotFormat' => 'pivot2D' ,
            'pivotRows' => $this->rowFields,
            'pivotColumns' => $this->columnFields,
            'pivotAggregates' => $this->aggregates,
            'pivotFieldDelimiter' => $separator,
            'columns' => $cMetas,
            'pivotExpandTrees' => $this->expandTrees,
        ]);

        foreach ($dsData as $row) {
            $this->next($row);
        }
    }

    protected function expandTreesToStateTrees($expandTrees)
    {
        $nullRowNode = $nullColumnNode = [
            "name" => null,
            "expanded" => true,
            "visible" => true,
            "parent" => null,
            "level" => -1,
            "children" => []
        ];

        $this->rowExpandNowNodes = $this->columnExpandNowNodes = [];
        $queue = [];
        array_push($queue, $expandTrees['row']);
        while (! empty($queue)) {
            $tmpNode = array_shift($queue);
            $tmpNode['expanded'] = true;
            $isRoot = $tmpNode['name'] === 'root';

            foreach ($tmpNode['children'] as $k => $childNode) {
                $childNode['level'] = $isRoot ? 0 : $tmpNode['level'] + 1;
                array_push($queue, $childNode);
            }

            if ($isRoot) {
                $nullRowNode['children'] = $tmpNode['children'];
                $tmpNode['children'] = [$nullRowNode];
                $tmpNode['level'] = -2;
                $expandTrees['row'] = $tmpNode;
            } 

            if (isset($tmpNode['expandNow']) && $tmpNode['expandNow']) {
                $this->rowExpandNowNodes[] = $tmpNode;
            }
        }

        $queue = [];
        array_push($queue, $expandTrees['column']);
        while (! empty($queue)) {
            $tmpNode = array_shift($queue);
            $tmpNode['expanded'] = true;
            $isRoot = $tmpNode['name'] === 'root';

            foreach ($tmpNode['children'] as $k => $childNode) {
                $childNode['level'] = $isRoot ? 0 : $tmpNode['level'] + 1;
                array_push($queue, $childNode);
            }

            if ($isRoot) {
                $nullColumnNode['children'] = $tmpNode['children'];
                $tmpNode['children'] = [$nullColumnNode];
                $expandTrees['column'] = $tmpNode;
            } 

            if (isset($tmpNode['expandNow']) && $tmpNode['expandNow']) {
                $this->columnExpandNowNodes[] = $tmpNode;
            }
        }
        // echo "expand to state trees<br>"; Util::prettyPrint($expandTrees);
        return $expandTrees;
    }

    protected function fillTree(& $node)
    {
        if (isset($node['parent'])) {
            $node['level'] = $node['parent']['level'] + 1;
        }
        if (empty($node['children'])) return;
        foreach ($node['children'] as $i => $childNode) {
            $node['children'][$i]['parent'] = & $node;
            $this->fillTree($node['children'][$i]);
        }
    }

    protected function treeToExpandNowNodes(& $node)
    {
        $expandNowNodes = [];
        if (isset($node['expandNow'])) {
            // echo "there's expandNow node<br>";
            $expandNowNodes[] = $node;
        } 
        foreach ($node['children'] as $i => $childNode) {
            $expandNowNodes = array_merge($expandNowNodes, $this->treeToExpandNowNodes($node['children'][$i]));
        }
        return $expandNowNodes;
    }

    protected function removeExpandNow(& $node)
    {
        if (isset($node['expandNow'])) {
            unset($node['expandNow']);
        } 
        foreach ($node['children'] as $i => $childNode) {
            $this->removeExpandNow($node['children'][$i]);
        }
    }

    protected function treeAndFieldLevelToExpandedNodes(& $rootNode, $fieldLevel)
    {
        $expandedNodes = [];
        $queue = [];
        // $rootNode['level'] = -2;
        $queue[] = $rootNode;
        // $level = 0;
        while ($tmpNode = array_shift($queue)) {
            $level = $tmpNode['level'];
            if ($level === $fieldLevel) {
                $expandedNodes[] = $tmpNode;
            } else if ($level < $fieldLevel) {
                foreach ($tmpNode['children'] as $i => $childNode) {
                    // $tmpNode['children'][$i]['level'] = $level + 1;
                    // $tmpNode['children'][$i]['parent'] = $tmpNode;
                    $queue[] = $tmpNode['children'][$i];
                }
            }
        }
        return $expandedNodes;
    }

    protected function nodeToWhereClause($nodeDimension, $node)
    {
        $whereClause = "1=1";
        $tmpNode = $node;
        while ($tmpNode['level'] > -2) {
            $level = $tmpNode['level'];
            $value = $tmpNode['name'];
            if ($level > -1) {
                $whereClause .= (" AND field$level = " .$this->finalSource->escapeStr($value));
            }
            // if (isset($tmpNode['parent'])) {
                $tmpNode = $tmpNode['parent'];
            // } else {
            //     echo "no parent tmpNode="; var_dump($tmpNode);
            //     break;
            // }
        }
        // var_dump($tmpNode);
        $root = $tmpNode;
        $fields = $this->{$nodeDimension."Fields"};
        foreach ($fields as $fieldLevel => $field) {
            if ($fieldLevel > $node['level']) break;
            $whereClause = str_replace("field$fieldLevel", $field, $whereClause);
        }

        return $whereClause;
    }

    protected function expandedNodesToWhereClause($dimension, $expandedNodes)
    {
        $whereClause = "1=0";
        foreach ($expandedNodes as $expandedNode) {
            $nodeWhereClause = $this->nodeToWhereClause($dimension, $expandedNode);
            $whereClause .= " OR ($nodeWhereClause)";
        }

        return $whereClause;
    }

    protected function nodesAndExpandedNodeToQuery($nodeDimension, $node, $expandedNodes)
    {
        $query = "";

        $nodeWhereClause = $this->nodeToWhereClause($nodeDimension, $node);
        // echo "node="; print_r($node); echo "<br>";
        // echo "nodeWhereClause=$nodeWhereClause<br>";
        $oppositeDimension = $nodeDimension === 'row' ? 'column' : 'row';
        $expandedNodesWhereClause = $this->expandedNodesToWhereClause($oppositeDimension, $expandedNodes);

        $fields = $nodeDimension === 'row' ? $this->rowFields : $this->columnFields;
        $oppositeFields = $nodeDimension === 'row' ? $this->columnFields : $this->rowFields;

        $selectFields = [];
        foreach ($fields as $fieldLevel => $field) {
            if (empty(trim($field))) continue;
            if ($fieldLevel > $node['level'] + 1) break;
            $selectFields[] = $field;
        }
        foreach ($oppositeFields as $fieldLevel => $oppositeField) {
            if (empty(trim($oppositeField))) continue;
            if ($fieldLevel > $expandedNodes[0]['level'] + 1) break;
            $selectFields[] = $oppositeField;
        }
        $selectFieldsStr = implode(", ", $selectFields);
        // echo "selectFieldsStr=$selectFieldsStr<br>";

        $selectMeasureFields = [];
        foreach ($this->aggregates as $field => $aggs) {
            foreach ($aggs as $agg)
                $selectMeasureFields[] = "$agg($field) as '{$agg}({$field})'";
        }
        $selectMeasureFieldsStr = implode(", ", $selectMeasureFields);

        $query .= "SELECT "
            . ($selectFieldsStr ? "$selectFieldsStr, " : "")
            . " $selectMeasureFieldsStr "
            . " FROM (" . $this->viewQuery .") TMP "
            . " WHERE ($nodeWhereClause) AND ($expandedNodesWhereClause)"
            . ($selectFieldsStr ? " GROUP BY $selectFieldsStr" : "")
            . ($selectFieldsStr ? " ORDER BY $selectFieldsStr" : "")
        ;

        return $query;
    }

    protected function nodeAndTreeToQueries($nodeDimension, $node, & $rootNode)
    {
        $queries = [];

        //Get expanded nodes at root level, i.e the root node itself
        $expandedNodes = $this->treeAndFieldLevelToExpandedNodes($rootNode, -2);
        // echo "<br>expandedNodes="; var_dump($expandedNodes); echo "<br>";
        if (! empty($expandedNodes)) {
            // echo "<br>nodeAndTreeToQueries: Level -2: not empty expandedNodes<br>";
            // var_dump($expandedNodes); echo "<br>";
            $query = $this->nodesAndExpandedNodeToQuery($nodeDimension, $node, $expandedNodes);
            // echo "<br>nodeAndTreeToQueries: Level -2: query=$query<br>";
            $queries[] = [
                'query' => $query,
                'node' => $node,
                'expandedNodes' => $expandedNodes
            ];
        }

        $nodeOppositeFields = $nodeDimension === 'column' ? $this->rowFields : $this->columnFields;
        // echo "<br>nodeOppositeFields="; var_dump($nodeOppositeFields); echo "<br>";
        //Get expanded nodes at each field level
        //If fields are empty, there's no expanded nodes
        foreach ($nodeOppositeFields as $fieldLevel => $field) {
            $expandedNodes = $this->treeAndFieldLevelToExpandedNodes($rootNode, $fieldLevel - 1);
            // echo "<br>expandedNodes="; var_dump($expandedNodes); echo "<br>";
            if (! empty($expandedNodes)) {
                $query = $this->nodesAndExpandedNodeToQuery($nodeDimension, $node, $expandedNodes);
                // echo "<br>nodeAndTreeToQueries: Level $fieldLevel: not empty expandedNodes<br>";
                // echo "<br>nodeAndTreeToQueries: Level $fieldLevel: query=$query<br>";
                $queries[] = [
                    'query' => $query,
                    'node' => $node,
                    'expandedNodes' => $expandedNodes
                ];
            }
        }

        return $queries;
    }

    
}
