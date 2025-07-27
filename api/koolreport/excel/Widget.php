<?php

namespace koolreport\excel;

class Widget extends \koolreport\core\Widget
{
    protected $namePrefix = "ExcelWidget";
    protected $type = 'widget';

    protected function onRender()
    {
        $this->useAutoName($this->namePrefix);
        $params = $this->params;
        $params['type'] = $this->type;
        $params['name'] = $this->name;
        $this->report->excelExportHandler->setWidgetParams($this->name, $params);
        $params = ['name' => $this->name];
        echo json_encode($params);
    }
}
