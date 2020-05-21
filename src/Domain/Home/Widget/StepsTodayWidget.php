<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Location\Steps\StepsMapper;

class StepsTodayWidget {

    private $logger;
    private $mapper;

    public function __construct(LoggerInterface $logger, StepsMapper $mapper) {
        $this->logger = $logger;
        $this->mapper = $mapper;
    }

    public function getContent() {
        $dateObj = new \DateTime('today');
        $date = $dateObj->format("Y-m-d");

        return $this->mapper->getStepsOfDate($date);
    }

}
