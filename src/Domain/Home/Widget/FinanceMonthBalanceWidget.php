<?php

namespace App\Domain\Home\Widget;

use App\Domain\Main\Translator;
use App\Domain\Finances\FinancesMapper;
use App\Domain\Base\CurrentUser;
use Slim\Routing\RouteParser;

class FinanceMonthBalanceWidget implements Widget {

    private $translation;
    private $router;
    private $current_user;
    private $mapper;

    public function __construct(Translator $translation, RouteParser $router, CurrentUser $user, FinancesMapper $mapper) {
        $this->translation = $translation;
        $this->router = $router;
        $this->current_user = $user;
        $this->mapper = $mapper;
    }

    public function getContent(?WidgetObject $widget = null) {
        $dateObj = new \DateTime('today');
        $year = $dateObj->format("Y");
        $month = $dateObj->format("m");

        $expenses = $this->mapper->statsMailBalance($this->current_user->getUser()->id, $month, $year, 0);
        $income   = $this->mapper->statsMailBalance($this->current_user->getUser()->id, $month, $year, 1);
        
        return round($income - $expenses, 2);
    }

    public function getTitle(?WidgetObject $widget = null) {
        return $this->translation->getTranslatedString("BALANCE_THIS_MONTH");
    }

    public function getOptions(?WidgetObject $widget = null) {
        return [];
    }

    public function getLink(?WidgetObject $widget = null) {
        return $this->router->urlFor('finances');
    }

}
