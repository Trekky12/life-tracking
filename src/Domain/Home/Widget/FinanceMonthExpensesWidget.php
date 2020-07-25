<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Finances\FinancesMapper;
use App\Domain\Base\CurrentUser;

class FinanceMonthExpensesWidget implements Widget {

    private $logger;
    private $translation;
    private $current_user;
    private $mapper;

    public function __construct(LoggerInterface $logger, Translator $translation, CurrentUser $user, FinancesMapper $mapper) {
        $this->logger = $logger;
        $this->translation = $translation;
        $this->current_user = $user;
        $this->mapper = $mapper;
    }

    public function getContent($id = null) {
        $dateObj = new \DateTime('today');
        $year = $dateObj->format("Y");
        $month = $dateObj->format("m");

        return $this->mapper->statsMailBalance($this->current_user->getUser()->id, $month, $year, 0);
    }

    public function getTitle($id = null) {
        return $this->translation->getTranslatedString("EXPENSES_THIS_MONTH");
    }

    public function getOptions() {
        return [];
    }

}
