<?php

namespace koolreport\visualquery;

use \koolreport\core\Utility as Util;

class VisualQueryHelper
{
    public function inputToParams()
    {
        $queryParams = [];
        function getInputsStartWith($prefix)
        {
            $vqNames = [];
            foreach ($_POST as $name => $value) {
                if (substr($name, 0, strlen($prefix)) === $prefix) {
                    $vqNames[] = $name;
                }
            }
            return $vqNames;
        }
        // echo "_POST = "; Util::prettyPrint($_POST);
        $vqNames = Util::get($_POST, 'visualqueries', []);
        foreach ($vqNames as $vqName) {
            $vqPrefix = $vqName . '_';
            $vqInputs = getInputsStartWith($vqPrefix);
            $vqValue = [];
            foreach ($vqInputs as $vqInput) {
                $vqValue[substr($vqInput, strlen($vqPrefix))] = $_POST[$vqInput];
            }
            $queryParams[$vqName] = $vqValue;
        }
        // echo "queryParams = "; Util::prettyPrint($queryParams);
        return $queryParams;
    }

    public function paramsToValue($params)
    {
        // Util::prettyPrint($params);
        if (!isset($params)) return null;

        $value = [
            'selectDistinct' => Util::get($params, 'distinct', false),
            'selectTables' => Util::get($params, 'selectTables', []),
            'selectFields' => Util::get($params, 'selectFields', []),
        ];

        $toggles = Util::get($params, 'filter_toggles', []);
        $validities = Util::get($params, 'filter_validities', []);
        $logics = Util::get($params, 'filter_logics', []);
        $fields = Util::get($params, 'filter_fields', []);
        $operators = Util::get($params, 'filter_operators', []);
        $value1s = Util::get($params, 'filter_value1s', []);
        $value2s = Util::get($params, 'filter_value2s', []);
        $filters = [];
        foreach ($fields as $i => $field) {
            $filter = [
                "field" => $field,
                "operator" => $operators[$i],
                "value1" => $value1s[$i],
                "value2" => $value2s[$i],
                "logic" => $logics[$i],
                "toggle" => $toggles[$i] === "on" ? true : false,
                "validity" => $validities[$i] == 1 ? true : false,
            ];
            $filters[] = $filter;
        }
        $brackets = Util::get($params, 'filter_brackets', []);
        foreach ($brackets as $i => $bracket) {
            if ($bracket === "(" || $bracket === ")") {
                array_splice($filters, $i, 0, $bracket);
            }
        }
        $value["filters"] = $filters;

        $toggles = Util::get($params, 'having_toggles', []);
        $validities = Util::get($params, 'having_validities', []);
        $logics = Util::get($params, 'having_logics', []);
        $fields = Util::get($params, 'having_fields', []);
        $operators = Util::get($params, 'having_operators', []);
        $value1s = Util::get($params, 'having_value1s', []);
        $value2s = Util::get($params, 'having_value2s', []);
        $havings = [];
        foreach ($fields as $i => $field) {
            $filter = [
                "field" => $field,
                "operator" => $operators[$i],
                "value1" => $value1s[$i],
                "value2" => $value2s[$i],
                "logic" => $logics[$i],
                "toggle" => $toggles[$i] === "on" ? true : false,
                "validity" => $validities[$i] == 1 ? true : false,
            ];
            $havings[] = $filter;
        }
        $brackets = Util::get($params, 'having_brackets', []);
        foreach ($brackets as $i => $bracket) {
            if ($bracket === "(" || $bracket === ")") {
                array_splice($havings, $i, 0, $bracket);
            }
        }
        $value["havings"] = $havings;

        $groupToggles = Util::get($params, 'group_toggles', []);
        $groupValidities = Util::get($params, 'group_validities', []);
        $fields = Util::get($params, 'group_fields', []);
        $aggregates = Util::get($params, 'group_aggregates', []);
        $groups = [];
        foreach ($fields as $i => $field) {
            $group = [
                "field" => $field,
                "aggregate" => $aggregates[$i]
            ];
            $group["toggle"] = $groupToggles[$i] === "on" ? true : false;
            $group["validity"] = $groupValidities[$i] == 1 ? true : false;
            $groups[] = $group;
        }
        $value["groups"] = $groups;

        $sortToggles = Util::get($params, 'sort_toggles', []);
        $sortValidities = Util::get($params, 'sort_validities', []);
        $fields = Util::get($params, 'sort_fields', []);
        $directions = Util::get($params, 'sort_directions', []);
        $sorts = [];
        foreach ($fields as $i => $field) {
            $sort = [
                "field" => $field,
                "direction" => $directions[$i]
            ];
            $sort["toggle"] = $sortToggles[$i] === "on" ? true : false;
            $sort["validity"] = $sortValidities[$i] == 1 ? true : false;
            $sorts[] = $sort;
        }
        $value["sorts"] = $sorts;

        $value['limit'] = [
            'toggle' => Util::get($params, "limit_toggle", false),
            'offset' => Util::get($params, "limit_offset", ""),
            'limit' => Util::get($params, "limit_limit", ""),
        ];

        $value['activeTab'] = Util::get($params, 'activeTab', 'tables');

        // Util::prettyPrint($value);
        return $value;
    }

    protected function replaceTAlias($field)
    {
        $arr = explode($this->separator, $field);
        $table = $arr[0];
        $tAlias = $this->tableMap[$table];
        $field = $arr[1];
        return "$tAlias.$field";
    }

    protected function replaceExpAlias($exp)
    {
        $table = $this->expTables[$exp];
        $tAlias = $this->tableMap[$table];
        $exp = str_ireplace("$table.", "$tAlias.", $exp);
        return $exp;
    }

    public function getFieldsAndLinks($schema)
    {
        $tables = Util::get($schema, "tables", []);
        $allFields = [];
        foreach ($tables as $table => $fields) {
            foreach ($fields as $field => $v) {
                Util::init($v, "alias", $field);
                // Util::init($v, 'as', $field);
                $allFields["$table.$field"] = $v;
            }
        }

        $relations = Util::get($schema, "relations", []);
        $tableLinks = [];
        foreach ($relations as $relation) {
            $table1 = explode($this->separator, $relation[0])[0];
            $table2 = explode($this->separator, $relation[2])[0];
            // $joinType = $relation[1];
            Util::init($tableLinks, $table1, []);
            $tableLinks[$table1][$table2] = [
                'join' => $relation[1],
                'field1' => $relation[0],
                'field2' => $relation[2]
            ];
            Util::init($tableLinks, $table2, []);
            $tableLinks[$table2][$table1] = [
                'join' => $relation[1],
                'field1' => $relation[2],
                'field2' => $relation[0]
            ];
        }
        // Util::prettyPrint($allFields);
        // Util::prettyPrint($tableLinks);
        return [$tables, $allFields, $tableLinks];
    }

    public function getAllSchemasFieldsAndLinks($schemas)
    {
        $allTables = [];
        $allTableLinks = [];
        $allTableFields = [];
        foreach ($schemas as $schema) {
            list($tables, $allFields, $tableLinks) =
                $this->getFieldsAndLinks($schema);
            $allTables = array_merge($allTables, $tables);
            $allTableFields = array_merge($allTableFields, $allFields);
            $allTableLinks = array_merge($allTableLinks, $tableLinks);
        }
        return [$allTables, $allTableFields, $allTableLinks];
    }

    protected function buildQueryFrom($qb, $tables)
    {
        $tableLinks = $this->tableLinks;
        // $t = 0;
        $this->tableMap = [];
        foreach ($tables as $i => $table) {
            if (!isset($this->allTables[$table])) continue;
            // $tAlias = "table_$t";
            $tAlias = $table;
            // $t++;
            $this->tableMap[$table] = $tAlias;
            if ($i === 0) {
                // $qb = DB::table("$table $tAlias");
                $qb = $qb->from("$table");
                continue;
            }
            for ($j = 0; $j < $i; $j++) {
                if (isset($tableLinks[$tables[$j]][$table])) {
                    $tableLink = $tableLinks[$tables[$j]][$table];
                    break;
                }
            }
            $qb = $qb->{$tableLink['join']}(
                // "$table $tAlias",
                "$table",
                $this->replaceTAlias($tableLink['field1']),
                "=",
                $this->replaceTAlias($tableLink['field2'])
            );
        }
        return $qb;
    }

    protected function buildQueryFields($qb, $fields)
    {
        $allFields = $this->allFields;
        $this->exps = [];
        $aliases = [];
        $this->expTables = [];
        foreach ($fields as $field) {
            if (!isset($allFields[$field])) continue;

            $alias = Util::get($allFields, [$field, 'alias']);
            $exp = Util::get($allFields, [$field, 'expression'], $field);
            $this->exps[] = $exp;
            $aliases[$exp] = $alias;
            $this->expTables[$exp] = explode($this->separator, $field)[0];
        }
        foreach ($this->exps as $exp) {
            $alias = $aliases[$exp];
            $exp = $this->replaceExpAlias($exp, $this->expTables, $this->tableMap);
            $qb = $qb->select($exp)->alias("\"$alias\"");
        }
        return $qb;
    }

    protected function buildQueryGroups($qb, $groups)
    {
        $allAggs = array_flip(["sum", "count", "count_distinct", "avg", "min", "max"]);
        $allFields = $this->allFields;
        $this->groupExps = [];
        $hasGroup = false;
        foreach ($groups as $group) {
            $toggle = Util::get($group, 'toggle', true);
            if (!$toggle) continue;
            $validity = Util::get($group, 'validity', true);
            if (!$validity) continue;
            $hasGroup = true;
            $field = $group["field"];
            $agg = $group["aggregate"];
            if (!isset($allFields[$field]) || !isset($allAggs[$agg])) continue;

            $exp = Util::get($allFields, [$field, 'expression'], $field);
            $alias = Util::get($allFields, [$field, 'alias'], $field);
            // echo "field=$field<br>";
            $this->expTables[$exp] = explode($this->separator, $field)[0];
            $exp = $this->replaceExpAlias($exp, $this->expTables, $this->tableMap);
            $groupAlias = "$agg($alias)";
            $qb = $qb->{$agg}($exp)->alias("\"$groupAlias\"");
            $this->groupExps["$agg($field)"] = "$agg($exp)";
            $this->expTables["$agg($exp)"] = explode($this->separator, $field)[0];
            $colMeta = $agg !== "count" ? $allFields[$field] : [
                "type" => "number"
            ];
            $colMeta["alias"] = $groupAlias;
            $resultCols[] = $colMeta;
        }
        if ($hasGroup && !empty($this->exps)) {
            $groups = "";
            foreach ($this->exps as $exp) {
                $exp = $this->replaceExpAlias($exp, $this->expTables, $this->tableMap);
                $groups .= ", $exp";
            }
            $groups = substr($groups, 2);
            $qb = $qb->groupBy($groups);
        }
        return $qb;
    }

    protected function buildQueryHavings($qb, $havings)
    {
        // echo "havings = "; Util::prettyPrint($havings);
        $allFields = $this->allFields;
        $selectFields = Util::get($this->params, 'selectFields', []);
        $groups = Util::get($this->params, 'groups', []);
        foreach ($havings as $filter) {
            // echo "having="; Util::prettyPrint($filter);
            if ($filter === "(") {
                // echo "open where bracket<br>";
                $qb->havingOpenBracket();
            } else if ($filter === ")") {
                // echo "close where bracket<br>";
                $qb->havingCloseBracket();
            } else {
                $toggle = Util::get($filter, "toggle", true);
                if (!$toggle) continue;
                $validity = Util::get($filter, "validity", true);
                if (!$validity) continue;
                $trueField = $field = Util::get($filter, 'field', Util::get($filter, 0));
                // echo "field=$field<br>";

                $isFieldValid = false;
                if (in_array($field, $selectFields)) $isFieldValid = true;
                foreach ($groups as $group) {
                    $toggle = Util::get($group, 'toggle', true);
                    if (!$toggle) continue;
                    $groupField = $group['field'];
                    // echo "groupField=$groupField<br>";
                    $agg = $group['aggregate'];
                    if ("{$agg}({$groupField})" === $field) {
                        $isFieldValid = true;
                        $trueField = $groupField;
                    }
                }
                if (!$isFieldValid) continue;

                $exp = Util::get($allFields, [$field, 'expression'], $field);
                // echo "exp=$exp<br>";
                $this->expTables[$exp] = explode($this->separator, $trueField)[0];
                // Util::prettyPrint($this->expTables);
                $exp = str_replace("{group}.", "", $exp);
                $exp = Util::get($this->groupExps, $exp, $exp);
                // echo "exp=$exp<br>";
                $exp = $this->replaceExpAlias($exp, $this->expTables, $this->tableMap);

                $op = Util::get($filter, 'operator', Util::get($filter, 1));
                $value1 = Util::get($filter, 'value1', Util::get($filter, 2));
                $value2 = Util::get($filter, 'value2', Util::get($filter, 3));
                $logic = Util::get($filter, 'logic', Util::get($filter, 4));
                // echo "op=$op<br>";
                switch ($op) {
                    case "=":
                    case "<>":
                    case ">":
                    case ">=":
                    case "<":
                    case "<=":
                        $qb = $logic === 'and' ?
                            $qb->having($exp, $op, $value1) :
                            $qb->orHaving($exp, $op, $value1);
                        break;
                    case "ctn":
                        $qb = $logic === 'and' ?
                            $qb->having($exp, 'like', "%$value1%") :
                            $qb->orHaving($exp, 'like', "%$value1%");
                        break;
                    case "nctn":
                        $qb = $logic === 'and' ?
                            $qb->having($exp, 'not like', "%$value1%") :
                            $qb->orHaving($exp, 'not like', "%$value1%");
                        break;
                    case "btw":
                        $qb = $logic === 'and' ?
                            $qb->havingRaw("$exp between ? and ?", [$value1, $value2]) :
                            $qb->orHavingRaw("$exp between ? and ?", [$value1, $value2]);
                        break;
                    case "nbtw":
                        $qb = $logic === 'and' ?
                            $qb->havingRaw("$exp not between ? and ?", [$value1, $value2]) :
                            $qb->orHavingRaw("$exp not between ? and ?", [$value1, $value2]);
                        break;
                    case "in":
                        $qb = $logic === 'and' ?
                            $qb->havingRaw("$exp in ?", explode(",", $value1)) :
                            $qb->orHavingRaw("$exp in ?", explode(",", $value1));
                        break;
                    case "nin":
                        $qb = $logic === 'and' ?
                            $qb->havingRaw("$exp not in", explode(",", $value1)) :
                            $qb->orHavingRaw("$exp not in", explode(",", $value1));
                        break;
                    case "null":
                        $qb = $logic === 'and' ?
                            $qb->havingRaw("$exp is null") :
                            $qb->orHavingRaw("$exp is null");
                        break;
                    case "nnull":
                        $qb = $logic === 'and' ?
                            $qb->havingRaw("$exp is not null") :
                            $qb->orHavingRaw("$exp is not null");
                        break;
                }
            }
        }
        return $qb;
    }

    protected function buildQueryFilters($qb, $filters)
    {
        $allFields = $this->allFields;
        // echo "filters = "; Util::prettyPrint($filters);
        foreach ($filters as $filter) {
            if ($filter === "(") {
                // echo "open where bracket<br>";
                $qb->whereOpenBracket();
            } else if ($filter === ")") {
                // echo "close where bracket<br>";
                $qb->whereCloseBracket();
            } else {
                $toggle = Util::get($filter, "toggle", true);
                if (!$toggle) continue;
                $validity = Util::get($filter, "validity", true);
                if (!$validity) continue;
                $field = Util::get($filter, 'field', Util::get($filter, 0));
                if (!isset($allFields[$field])) continue;

                // $field = $this->replaceTAlias($field, $this->tableMap);
                $exp = Util::get($allFields, [$field, 'expression'], $field);
                $this->expTables[$exp] = explode($this->separator, $field)[0];
                $exp = $this->replaceExpAlias($exp, $this->expTables, $this->tableMap);
                $op = Util::get($filter, 'operator', Util::get($filter, 1));
                $value1 = Util::get($filter, 'value1', Util::get($filter, 2));
                $value2 = Util::get($filter, 'value2', Util::get($filter, 3));
                $logic = Util::get($filter, 'logic', Util::get($filter, 4));
                // echo "op=$op<br>";
                switch ($op) {
                    case "=":
                    case "<>":
                    case ">":
                    case ">=":
                    case "<":
                    case "<=":
                        $qb = $logic === 'and' ?
                            $qb->where($exp, $op, $value1) :
                            $qb->orWhere($exp, $op, $value1);;
                        break;
                    case "ctn":
                        $qb = $logic === 'and' ?
                            $qb->where($exp, 'like', "%$value1%") :
                            $qb->orWhere($exp, 'like', "%$value1%");
                        break;
                    case "nctn":
                        $qb = $logic === 'and' ?
                            $qb->where($exp, 'not like', "%$value1%") :
                            $qb->orWhere($exp, 'not like', "%$value1%");
                        break;
                    case "btw":
                        $qb = $logic === 'and' ?
                            $qb->whereBetween($exp, [$value1, $value2]) :
                            $qb->orWhereBetween($exp, [$value1, $value2]);
                        break;
                    case "nbtw":
                        $qb = $logic === 'and' ?
                            $qb->whereNotBetween($exp, [$value1, $value2]) :
                            $qb->orWhereNotBetween($exp, [$value1, $value2]);
                        break;
                    case "in":
                        $qb = $logic === 'and' ?
                            $qb->whereIn($exp, explode(",", $value1)) :
                            $qb->orWhereIn($exp, explode(",", $value1));
                        break;
                    case "nin":
                        $qb = $logic === 'and' ?
                            $qb->whereNotIn($exp, explode(",", $value1)) :
                            $qb->orWhereNotIn($exp, explode(",", $value1));
                        break;
                    case "null":
                        $qb = $logic === 'and' ?
                            $qb->whereNull($exp) :
                            $qb->orWhereNull($exp);
                        break;
                    case "nnull":
                        $qb = $logic === 'and' ?
                            $qb->whereNotNull($exp) :
                            $qb->orWhereNotNull($exp);
                        break;
                }
            }
        }
        return $qb;
    }

    protected function buildQuerySorts($qb, $sorts)
    {
        $allFields = $this->allFields;
        $selectFields = Util::get($this->params, 'selectFields', []);
        $groups = Util::get($this->params, 'groups', []);
        // Util::prettyPrint($groups);
        foreach ($sorts as $sort) {
            $toggle = Util::get($sort, 'toggle', true);
            if (!$toggle) continue;
            $validity = Util::get($sort, "validity", true);
            if (!$validity) continue;
            $field = $sort["field"];
            $dir = strtoupper(trim($sort["direction"]));
            if ($dir !== "ASC" && $dir !== "DESC") $dir = "ASC";

            $isFieldValid = false;
            if (in_array($field, $selectFields)) $isFieldValid = true;
            foreach ($groups as $group) {
                $toggle = Util::get($group, 'toggle', true);
                if (!$toggle) continue;
                $groupField = $group["field"];
                $agg = $group["aggregate"];
                if ("{$agg}({$groupField})" === $field) $isFieldValid = true;
            }
            if (!$isFieldValid) continue;

            $exp = Util::get($allFields, [$field, 'expression'], $field);
            $exp = Util::get($this->groupExps, $exp, $exp);
            $exp = $this->replaceExpAlias($exp, $this->expTables, $this->tableMap);
            $exp = str_replace("count_distinct(", "count(distinct ", $exp);
            $qb = $qb->orderBy($exp, $dir);
        }
        return $qb;
    }

    protected function buildQueryLimitOffset($qb, $limit, $offset)
    {
        if (is_numeric($offset) && is_numeric($limit)) {
            $qb = $qb->offset($offset)->limit($limit);
        }
        return $qb;
    }

    public function valueToQueryBuilder($schemas, $params)
    {
        $qb = new \koolreport\querybuilder\Query();

        $qb->setSchemas($schemas);
        $this->separator = Util::get($schemas, "separator", ".");

        list($this->allTables, $this->allFields, $this->tableLinks) =
            $this->getAllSchemasFieldsAndLinks($schemas);
        $this->params = $params;
        // Util::prettyPrint($params);

        $selectDistinct = Util::get($params, 'selectDistinct', false);
        if ($selectDistinct) $qb->distinct();

        $selectTables = Util::get($params, 'selectTables', []);
        $qb = $this->buildQueryFrom($qb, $selectTables);

        $selectFields = Util::get($params, 'selectFields', []);
        $qb = $this->buildQueryFields($qb, $selectFields);

        $groups = Util::get($params, 'groups', []);
        $qb = $this->buildQueryGroups($qb, $groups);

        $havings = Util::get($params, 'havings', []);
        $qb = $this->buildQueryHavings($qb, $havings);

        $filters = Util::get($params, 'filters', []);
        $qb = $this->buildQueryFilters($qb, $filters);

        $sorts = Util::get($params, 'sorts', []);
        $qb = $this->buildQuerySorts($qb, $sorts);

        $limit = Util::get($params, 'limit', []);
        $limitToggle = Util::get($limit, 'toggle', false);
        if ($limitToggle) {
            $limitLimit = Util::get($limit, 'limit', null);
            $limitOffset = Util::get($limit, 'offset', null);
            $qb = $this->buildQueryLimitOffset($qb, $limitLimit, $limitOffset);
        }

        return $qb;
    }
}
