<?php

namespace koolreport\excel;

use \koolreport\core\Utility as Util;

class Chart extends Widget
{
    protected $type = "chart";

    protected function onRender()
    {
        $chartType = Util::getClassName($this);
        $this->params['chartType'] = $chartType;
    
        parent::onRender();
    }
}
