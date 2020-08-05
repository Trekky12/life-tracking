<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Location\Steps\StepsMapper;

class StepsTodayWidget implements Widget {

    private $logger;
    private $translation;
    private $mapper;

    public function __construct(LoggerInterface $logger, Translator $translation, StepsMapper $mapper) {
        $this->logger = $logger;
        $this->translation = $translation;
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
    
    public function getOptions() {
        return [];
    }

}
