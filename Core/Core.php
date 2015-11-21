<?php

namespace Core;

use Core\Controllers\ControllerCore;

class Core 
{
    public function getData() 
    {
        $Core = new ControllerCore();
        return $Core->getData();
    }
    
    public function unfiltered()
    {
        $Core = new ControllerCore();
        return $Core->unfilteredData();
    }
    
    public function getTitles()
    {
        $Core = new ControllerCore();
        return $Core->getTitles();
    }
}
