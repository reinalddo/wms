<?php

namespace koolreport\amazing;

trait Theme
{
    public function __constructAmazingTheme()
    {
        $this->theme = new AmazingTheme($this);
        if(method_exists($this,"onAmazingThemeInit")) {
            $this->onAmazingThemeInit();
        }
    }
}