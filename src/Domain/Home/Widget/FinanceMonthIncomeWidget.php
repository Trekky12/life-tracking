<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Finances\FinancesMapper;
use App\Domain\Base\CurrentUser;

class FinanceMonthIncomeWidget implements Widget {

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

    public function getContent(WidgetObject $widget = null) {
        $dateObj = new \DateTime('today');
        $year = $dateObj->format("Y");
        $month = $dateObj->format("m");

        return $this->mapper->statsMailBalance($this->current_user->getUser()->id, $month, $year, 1);
    }

    public function getTitle(WidgetObject $widget = null) {
        return $this->translation->getTranslatedString("INCOME_THIS_MONTH");
    }

    public function getOptions() {
        return [];
    }

}
