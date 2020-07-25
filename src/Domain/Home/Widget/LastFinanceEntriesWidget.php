<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Finances\FinancesMapper;

class LastFinanceEntriesWidget implements Widget {

    private $logger;
    private $translation;
    private $mapper;

    public function __construct(LoggerInterface $logger, Translator $translation, FinancesMapper $mapper) {
        $this->logger = $logger;
        $this->translation = $translation;
        $this->mapper = $mapper;
    }

    public function getContent($id = null) {
        return $this->mapper->statsLastExpenses(5);
    }

    public function getTitle($id = null) {
        return $this->translation->getTranslatedString("LAST_5_EXPENSES");
    }

    public function getOptions() {
        return [];
    }

}
