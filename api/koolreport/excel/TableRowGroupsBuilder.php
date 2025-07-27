<?php

namespace koolreport\excel;

use \koolreport\core\Utility as Util;

class TableRowGroupsBuilder
{

    public function setProperties($properties)
    {
        foreach ($properties as $k => $v) {
            $this->{$k} = $v;
        }
        return $this;
    }

    protected function initValue($operator)
    {
        switch ($operator) {
            case 'min':
                return PHP_INT_MAX;
            case 'max':
                return PHP_INT_MIN;
            case 'sum':
            case 'count':
            case 'avg':
            default:
                return 0;
        }
    }

    protected function aggValue($operator, $aggValue, $value = null)
    {
        switch ($operator) {
            case 'min':
                return min($aggValue, $value);
            case 'max':
                return max($aggValue, $value);
            case 'count':
            case 'count percent':
                return $aggValue + 1;
            case 'avg':
            case 'sum':
            case 'sum percent':
            default:
                return (float) $aggValue + (float) $value;
        }
    }

    public function buildNumberOfGroupColumns()
    {
        $rowGroups = $this->rowGroups;
        $this->rowGroupFields = array_keys($rowGroups);
        $this->numberOfRowGroups = count($rowGroups);

        $this->hasRowGroupTopBottom = [];
        $this->numberOfRowGroupColumns = [0];
        $groupOrder = 0;
        foreach ($rowGroups as $groupInfo) {
            $top = Util::get($groupInfo, "top");
            $bottom = Util::get($groupInfo, "bottom");
            $this->hasRowGroupTopBottom[] = !empty($top) || !empty($bottom);
            $this->numberOfRowGroupColumns[$groupOrder + 1] =
                $this->numberOfRowGroupColumns[$groupOrder] +
                ($this->hasRowGroupTopBottom[$groupOrder] ? 1 : 0);
            $groupOrder++;
        }
        $this->totalRowGroupColumns = $this->numberOfRowGroupColumns[$this->numberOfRowGroups];
        return $this;
    }

    public function buildAggregates()
    {
        $this->aggregates = [];
        $this->ds->popStart();
        while ($dataRow = $this->ds->pop()) {
            $aggGroupsValue = "";
            foreach ($this->rowGroups as $groupField => $groupInfo) {
                $calculate = Util::get($groupInfo, 'calculate', []);
                $curGrValue = Util::get($dataRow, $groupField, "");
                $aggGroupsValue .= $curGrValue;
                foreach ($calculate as $calName => $aggregate) {
                    $aggOperator = $aggregate[0];
                    $aggField = $aggregate[1];
                    $initValue = $this->initValue($aggOperator);
                    $curAggValue = Util::init($this->aggregates, [$calName, $aggGroupsValue], $initValue);
                    $rowValue = Util::get($dataRow, $aggField);
                    $this->aggregates[$calName][$aggGroupsValue] =
                        $this->aggValue($aggOperator, $curAggValue, $rowValue);
                }
            }
        }
        return $this;
    }

    protected function replaceGroupValueAndAggregates($tpl)
    {
        $grPos = $this->groupPos;
        $grField = $this->rowGroupFields[$this->groupOrder];
        $grInfo = $this->rowGroups[$grField];

        $lastGrVal = Util::get($grInfo, 'lastGroupValue');
        $rowGrVal = Util::get($this->dataRow, $grField);
        $curGrVal = $grPos === 'top' ? $rowGrVal : $lastGrVal;
        $tpl = str_replace("{{$grField}}", $curGrVal, $tpl);

        $curRow = $grPos === 'top' ? $this->dataRow : $this->prevDataRow;
        $aggGroupsValue = "";
        for ($prevGrOrder = 0; $prevGrOrder <= $this->groupOrder; $prevGrOrder++) {
            $prevGrValue = Util::get($curRow, $this->rowGroupFields[$prevGrOrder], "");
            $aggGroupsValue .= $prevGrValue;
        }
        $calculate = Util::get($grInfo, 'calculate', []);
        foreach ($calculate as $calName => $aggregate) {
            $aggValue = Util::get($this->aggregates, [$calName, $aggGroupsValue]);
            $tpl = str_replace("{{$calName}}", $aggValue, $tpl);
        }

        return $tpl;
    }

    protected function buildThisGroupRow()
    {
        $grPos = $this->groupPos;
        $grOrder = $this->groupOrder;
        $grField = $this->rowGroupFields[$grOrder];
        $grInfo = $this->rowGroups[$grField];
        $rgStyle = Util::get($this->tableStyle, 'rowGroup', []);
        $emptyCell = [];

        $groupRow = array_fill(0, $this->startCol + $this->numberOfRowGroupColumns[$grOrder], $emptyCell);

        if ($this->hasRowGroupTopBottom[$grOrder]) {
            $grTpl = Util::get($grInfo, $grPos, "");
            $grTpl = $this->replaceGroupValueAndAggregates($grTpl);
            $grStyleArr = Util::map($rgStyle, [$grField, $grTpl, null], []);
            $cell = ["cellValue" => $grTpl, "styleArray" => $grStyleArr];
            $groupRow[] = $cell;
        }

        for ($nextGrOrder = $grOrder + 1; $nextGrOrder < $this->numberOfRowGroups; $nextGrOrder++) {
            if ($this->hasRowGroupTopBottom[$nextGrOrder]) {
                $groupRow[] = $emptyCell;
            }
        }

        $grColTplsName = $grPos === 'top' ? 'columnTops' : 'columnBottoms';
        $grColTpls = Util::get($grInfo, $grColTplsName, []);
        foreach ($this->expColKeys as $colKey) {
            $grColTpl = Util::get($grColTpls, $colKey, "");
            $grColTpl = $this->replaceGroupValueAndAggregates($grColTpl);
            $grStyleArr = Util::map($rgStyle, [$grField, $grColTpl, $colKey], []);
            $cell = ["cellValue" => $grColTpl, "styleArray" => $grStyleArr];
            $groupRow[] = $cell;
        }

        if (empty($grTpl) && empty($grColTpls)) {
            $groupRow = null;
        }

        return $groupRow;
    }

    protected function isLastGroupValueDifferent($grOrder)
    {
        for ($prevGrOrder = 0; $prevGrOrder <= $grOrder; $prevGrOrder++) {
            $grField = $this->rowGroupFields[$prevGrOrder];
            $lastGrVal = Util::get($this->rowGroups, [$grField, 'lastGroupValue']);
            $rowGrVal = Util::get($this->dataRow, $grField);

            $isLastGroupValueDifferent = $lastGrVal === null || $rowGrVal !== $lastGrVal;
            if ($isLastGroupValueDifferent) return true;
        }
        return false;
    }

    public function buildTopGroupRows($prevDataRow, $dataRow)
    {
        $this->prevDataRow = $prevDataRow;
        $this->dataRow = $dataRow;
        $topGroupRows = [];
        for ($grOrder = 0; $grOrder < $this->numberOfRowGroups; $grOrder++) {
            if ($this->isLastGroupValueDifferent($grOrder)) {
                $this->groupOrder = $grOrder;
                $this->groupPos = "top";
                $topGroupRow = $this->buildThisGroupRow();
                if (isset($topGroupRow)) $topGroupRows[] = $topGroupRow;
            }
        }
        return $topGroupRows;
    }

    public function buildBottomGroupRows($prevDataRow, $dataRow)
    {
        if (!isset($prevDataRow)) return [];
        $this->prevDataRow = $prevDataRow;
        $this->dataRow = $dataRow;
        $bottomGroupRows = [];
        for ($grOrder = $this->numberOfRowGroups - 1; $grOrder >= 0; $grOrder--) {
            if ($this->isLastGroupValueDifferent($grOrder)) {
                $this->groupOrder = $grOrder;
                $this->groupPos = "bottom";
                $bottomGroupRow = $this->buildThisGroupRow();
                if (isset($bottomGroupRow)) $bottomGroupRows[] = $bottomGroupRow;
            }
        }
        return $bottomGroupRows;
    }

    public function setLastGroupValues()
    {
        for ($grOrder = 0; $grOrder < $this->numberOfRowGroups; $grOrder++) {
            if ($this->isLastGroupValueDifferent($grOrder)) {
                $grField = $this->rowGroupFields[$grOrder];
                $rowGrVal = Util::get($this->dataRow, $grField);
                Util::set($this->rowGroups, [$grField, 'lastGroupValue'], $rowGrVal);            
            }
        }
    }
}
