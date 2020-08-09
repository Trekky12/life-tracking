<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Finances\FinancesMapper;
use Slim\Routing\RouteParser;

class LastFinanceEntriesWidget implements Widget {

    private $logger;
    private $translation;
    private $router;
    private $mapper;

    public function __construct(LoggerInterface $logger, Translator $translation, RouteParser $router, FinancesMapper $mapper) {
        $this->logger = $logger;
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

    public function getOptions() {
        return [];
    }

    public function getLink(WidgetObject $widget = null) {
        return $this->router->urlFor('finances');
    }

}
