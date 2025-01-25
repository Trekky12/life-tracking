<?php

namespace App\Domain\Home\Widget;

use App\Domain\Main\Translator;
use App\Domain\Splitbill\Bill\BillMapper;
use App\Domain\Splitbill\Group\SplitbillGroupService;
use Slim\Routing\RouteParser;

class SplittedBillsBalanceWidget implements Widget {

    private $translation;
    private $router;
    private $group_service;
    private $bill_mapper;
    private $groups = [];

    public function __construct(Translator $translation, RouteParser $router, SplitbillGroupService $group_service, BillMapper $bill_mapper) {
        $this->translation = $translation;
        $this->router = $router;
        $this->group_service = $group_service;
        $this->bill_mapper = $bill_mapper;

        $this->groups = $this->createList();
    }

    private function createList() {
        $user_groups = $this->group_service->getUserElements();

        $groups = $this->group_service->getAll();

        $result = [];

        foreach ($user_groups as $group_id) {
            $group = $groups[$group_id];

            $result[$group_id] = ["name" => $group->name, "hash" => $group->getHash()];
        }

        return $result;
    }

    public function getListItems() {
        return array_keys($this->groups);
    }

    public function getContent(?WidgetObject $widget = null) {
        $id = $widget->getOptions()["group"];

        $balances = $this->bill_mapper->getBalances();
        $balance = array_key_exists($id, $balances) ? $balances[$id] : null;

        if (!is_null($balance)) {
            return round($balance["balance"], 2);
        }
        return 0;
    }

    public function getTitle(?WidgetObject $widget = null) {
        $id = $widget->getOptions()["group"];
        return sprintf("%s | %s", $this->translation->getTranslatedString("SPLITBILLS"), $this->groups[$id]["name"]);
    }

    public function getOptions(?WidgetObject $widget = null) {
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

    public function getLink(?WidgetObject $widget = null) {
        $id = $widget->getOptions()["group"];
        return $this->router->urlFor('splitbill_bills', ["group" => $this->groups[$id]["hash"]]);
    }
}
