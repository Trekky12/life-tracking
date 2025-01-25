<?php

namespace App\Domain\Home\Widget;

use App\Domain\Main\Translator;
use App\Domain\Location\Steps\StepsMapper;
use Slim\Routing\RouteParser;

class StepsTodayWidget implements Widget {

    private $translation;
    private $router;
    private $mapper;

    public function __construct(Translator $translation, RouteParser $router, StepsMapper $mapper) {
        $this->translation = $translation;
        $this->router = $router;
        $this->mapper = $mapper;
    }

    public function getContent(?WidgetObject $widget = null) {
        $dateObj = new \DateTime('today');
        $date = $dateObj->format("Y-m-d");
        
        $steps_of_day = $this->mapper->getStepsOfDate($date);

        return $steps_of_day ? $steps_of_day : 0 ;
    }

    public function getTitle(?WidgetObject $widget = null) {
        return $this->translation->getTranslatedString("STEPS_TODAY");
    }
    
    public function getOptions(?WidgetObject $widget = null) {
        return [];
    }

    public function getLink(?WidgetObject $widget = null) {
        return $this->router->urlFor('steps');
    }

}
