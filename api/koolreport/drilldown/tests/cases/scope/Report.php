<?php

require_once "../../../../core/tests/cases/DbReport.php";

class Report extends DbReport
{
    use \koolreport\amazing\Theme;
    use \koolreport\inputs\Bindable;
    use \koolreport\inputs\POSTBinding;
    protected function bindParamsToInputs()
    {
        return [
            "textBox"
        ];
    }
    protected function defaultParamValues()
    {
        return [
            "textBox"=>"KoolReport is great",
        ];
    }
}