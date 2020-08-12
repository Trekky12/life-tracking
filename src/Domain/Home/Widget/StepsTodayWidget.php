<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Location\Steps\StepsMapper;
use Slim\Routing\RouteParser;

class StepsTodayWidget implements Widget {

    private $logger;
    private $translation;
    private $router;
    private $mapper;

    public function __construct(LoggerInterface $logger, Translator $translation, RouteParser $router, StepsMapper $mapper) {
        $this->logger = $logger;
        $this->translation = $translation;
        $this->router = $router;
        $this->mapper = $mapper;
    }

    public function getContent(WidgetObject $widget = null) {
        $dateObj = new \DateTime('today');
        $date = $dateObj->format("Y-m-d");

        return $this->mapper->getStepsOfDate($date);
    }

    public function getTitle(WidgetObject $widget = null) {
        return $this->translation->getTranslatedString("STEPS_TODAY");
    }
    
    public function getOptions(WidgetObject $widget = null) {
        return [];
    }

    public function getLink(WidgetObject $widget = null) {
        return $this->router->urlFor('steps');
    }

}
