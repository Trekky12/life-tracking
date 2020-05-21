<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Finances\FinancesMapper;
use App\Domain\Base\CurrentUser;

class FinanceMonthIncomeWidget {

    private $logger;
    private $current_user;
    private $mapper;
    

    public function __construct(LoggerInterface $logger, CurrentUser $user, FinancesMapper $mapper) {
        $this->logger = $logger;
        $this->current_user = $user;
        $this->mapper = $mapper;
    }

    public function getContent() {
        $dateObj = new \DateTime('today');
        $year = $dateObj->format("Y");
        $month = $dateObj->format("m");
        
        return $this->mapper->statsMailBalance($this->current_user->getUser()->id, $month, $year, 1);
    }

}
