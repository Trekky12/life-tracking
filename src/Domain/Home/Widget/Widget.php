<?php

namespace App\Domain\Home\Widget;

interface Widget {

    public function getContent(WidgetObject $widget = null);

    public function getTitle(WidgetObject $widget = null);
    
    public function getOptions();
}
