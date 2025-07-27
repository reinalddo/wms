<?php

namespace koolreport\visualquery;

use \koolreport\core\Utility as Util;

class VisualQuery extends \koolreport\core\Widget
{
    public function version()
    {
        return "2.0.0";
    }

    protected function resourceSettings()
    {
        $themeBase = $this->getThemeBase();
        if (empty($themeBase)) $themeBase = 'bs3';
        // echo "themeBase=$themeBase<br>";

        $resources = [
            "library" => array("jQuery"),
            "folder" => "assets",
            "js" => ["js/visualquery.js", "jqueryui/jquery-ui.min.js"],
            "css" => ["jqueryui/jquery-ui.min.css"]
        ];

        $inputsPath = "inputs";
        // $inputsPath = "Inputs_clients";
        $bootstrapMultiselectDir = $themeBase === 'bs3' ?
            'bootstrap-multiselect' : 'bootstrap-multiselect-0.9';

        if ($themeBase === 'bs3') {
            $inputsResources = [
                "js" => array(
                    "$inputsPath/$bootstrapMultiselectDir/bootstrap-multiselect.js",
                    "$inputsPath/moment/moment.min.js",
                    "$inputsPath/moment/locales.min.js",
                    array(
                        "$inputsPath/datetimepicker/js/datetimepicker.min.js",
                        "$inputsPath/datetimepicker/js/linkedpicker.js",
                    ),
                ),
                "css" => array(
                    "$inputsPath/$bootstrapMultiselectDir/bootstrap-multiselect.css",
                    "$inputsPath/datetimepicker/css/datetimepicker.min.css",
                )
            ];
        } else if ($themeBase === 'bs4') {
            // $inputsResources['css'][] = "$inputsPath/$bootstrapMultiselectDir/additional.bs4.css";
            $resources['library'][] = 'font-awesome';
            $inputsResources = [
                "js" => array(
                    "$inputsPath/$bootstrapMultiselectDir/bootstrap-multiselect.js",
                    "$inputsPath/moment/moment.min.js",
                    "$inputsPath/moment/locales.min.js",
                    array(
                        "$inputsPath/datetimepicker/js/datetimepicker.bs4.min.js",
                        "$inputsPath/datetimepicker/js/linkedpicker.js",
                    ),
                ),
                "css" => array(
                    "$inputsPath/$bootstrapMultiselectDir/bootstrap-multiselect.css",
                    "$inputsPath/datetimepicker/css/datetimepicker.bs4.min.css",
                )
            ];
        }


        return array_merge_recursive(
            $resources,
            $inputsResources
        );
    }

    protected function onInit()
    {
        $themeBase = $this->getThemeBase();
        if (empty($themeBase)) $themeBase = 'bs3';
        $this->themeBase = $themeBase;
        // echo "themeBase={$this->themeBase}<br>";
        $this->name = Util::get($this->params, 'id', null);
        $this->name = Util::get($this->params, 'name', $this->name);

        $report = $this->getReport();
        $queryParams = Util::get($report->queryParams, $this->name);
        $this->value = $report->paramsToValue($queryParams);
        // echo "this->value = "; Util::prettyPrint($this->value);
        $this->defaultValue = Util::get($this->params, 'defaultValue');
        $this->activeTab = strtolower(Util::get($this->value, 'activeTab',
            Util::get($this->params, 'activeTab', 'tables')));
        // echo "this->activeTab = {$this->activeTab}<br>";
        $schema = Util::get($this->params, "schema");
        if (is_string($schema) && method_exists($report, 'defineSchemas')) {
            $schemas = $report->defineSchemas();
            $this->schema = Util::get($schemas, $schema);
            $this->separator = Util::get($schemas, "separator", ".");
        } else if (is_array($schema)) {
            // $this->schema = $schema;
        }
        if (isset($this->value['schema'])) {
            if (json_decode($this->value['schema'], true) != $this->schema) {
                throw new \Exception("Schema not correct");
            }
        }
        list($this->tables, $this->allFields, $this->tableLinks) =
            $report->vqHelper->getFieldsAndLinks($this->schema);
    }

    protected function onRender()
    {
        $this->template("VisualQuery", [
            "name" => $this->name
        ]);
    }
}
