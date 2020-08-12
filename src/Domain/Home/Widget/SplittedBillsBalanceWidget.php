<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Base\CurrentUser;
use App\Domain\Splitbill\Bill\BillMapper;
use App\Domain\Splitbill\Group\SplitbillGroupService;
use Slim\Routing\RouteParser;

class SplittedBillsBalanceWidget implements Widget {

    private $logger;
    private $translation;
    private $router;
    private $current_user;
    private $group_service;
    private $bill_mapper;
    private $groups = [];

    public function __construct(LoggerInterface $logger, Translator $translation, RouteParser $router, CurrentUser $user, SplitbillGroupService $group_service, BillMapper $bill_mapper) {
        $this->logger = $logger;
        $this->translation = $translation;
        $this->router = $router;
        $this->current_user = $user;
        $this->group_service = $group_service;
        $this->bill_mapper = $bill_mapper;

        $this->groups = $this->createList();
    }

    private function createList() {
        $user_groups = $this->group_service->getUserGroups();

        $groups = $this->group_service->getGroups();

        $balances = $this->bill_mapper->getBalances();
        $result = [];

        foreach ($user_groups as $group_id) {
            $group = $groups[$group_id];
            $balance = array_key_exists($group_id, $balances) ? $balances[$group_id] : null;

            if (!is_null($balance) && $balance["balance"] > 0) {
                $result[$group_id] = ["name" => $group->name, "hash" => $group->getHash(), "balance" => $balance["balance"]];
            }
        }

        return $result;
    }

    public function getListItems() {
        return array_keys($this->groups);
    }

    public function getContent(WidgetObject $widget = null) {
        $id = $widget->getOptions()["group"];
        return $this->groups[$id]["balance"];
    }

    public function getTitle(WidgetObject $widget = null) {
        $id = $widget->getOptions()["group"];
        return sprintf("%s | %s", $this->translation->getTranslatedString("SPLITBILLS"), $this->groups[$id]["name"]);
    }

    public function getOptions(WidgetObject $widget = null) {
        return [
            [
                "label" => $this->translation->getTranslatedString("SPLITBILL_GROUPS"),
                "data" => $this->createList(),
                "value" => !is_null($widget) ? $widget->getOptions()["group"] : null,
                "name" => "group",
                "type" => "select"
            ]
        ];
    }

    public function getLink(WidgetObject $widget = null) {
        $id = $widget->getOptions()["group"];
        return $this->router->urlFor('splitbill_bills', ["group" => $this->groups[$id]["hash"]]);
    }

}
