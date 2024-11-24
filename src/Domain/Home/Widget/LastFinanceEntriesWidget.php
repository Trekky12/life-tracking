<?php

namespace App\Domain\Home\Widget;

use App\Domain\Main\Translator;
use App\Domain\Finances\FinancesMapper;
use Slim\Routing\RouteParser;

class LastFinanceEntriesWidget implements Widget {

    private $translation;
    private $router;
    private $mapper;

    public function __construct(Translator $translation, RouteParser $router, FinancesMapper $mapper) {
        $this->translation = $translation;
        $this->router = $router;
        $this->mapper = $mapper;
    }

    public function getContent(WidgetObject $widget = null) {
        return $this->mapper->statsLastExpenses(5);
    }

    public function getTitle(WidgetObject $widget = null) {
        return $this->translation->getTranslatedString("LAST_5_EXPENSES");
    }

    public function getOptions(WidgetObject $widget = null) {
        return [];
    }

    public function getLink(WidgetObject $widget = null) {
        return $this->router->urlFor('finances');
    }

}
