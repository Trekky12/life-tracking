<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Finances\FinancesMapper;

class LastFinanceEntriesWidget {

    private $logger;
    private $mapper;

    public function __construct(LoggerInterface $logger, FinancesMapper $mapper) {
        $this->logger = $logger;
        $this->mapper = $mapper;
    }

    public function getContent() {
        return $this->mapper->statsLastExpenses(5);
    }

}
