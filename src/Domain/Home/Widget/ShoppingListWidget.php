<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Base\CurrentUser;
use App\Domain\Recipes\Shoppinglist\ShoppinglistService;
use App\Domain\Board\Card\CardMapper;
use Slim\Routing\RouteParser;

class ShoppingListWidget implements Widget
{

    private $translation;
    private $router;
    private $shoppinglist_service;
    private $shoppinglists;

    public function __construct(
        LoggerInterface $logger,
        Translator $translation,
        RouteParser $router,
        CurrentUser $user,
        ShoppinglistService $shoppinglist_service
    ) {
        $this->logger = $logger;
        $this->translation = $translation;
        $this->router = $router;
        $this->current_user = $user;
        $this->shoppinglist_service = $shoppinglist_service;

        $this->shoppinglists = $this->createList();
    }

    private function createList()
    {
        $shoppinglists = $this->shoppinglist_service->getAll();

        $result = [];
        foreach ($shoppinglists as $shoppinglist) {
            $result[$shoppinglist->id] = ["name" => $shoppinglist->name, "hash" => $shoppinglist->getHash()];
        }

        return $result;
    }

    public function getListItems()
    {
        return array_keys($this->shoppinglists);
    }

    public function getContent(WidgetObject $widget = null)
    {
        $id = $widget->getOptions()["shoppinglist"];

        $entries = $this->shoppinglist_service->retrieveShoppingListEntries($id, null, null);

        return ["hash" => $this->shoppinglists[$id]["hash"], "entries" => $entries];
    }

    public function getTitle(WidgetObject $widget = null)
    {
        $id = $widget->getOptions()["shoppinglist"];

        return sprintf("%s", $this->shoppinglists[$id]["name"]);
    }

    public function getOptions(WidgetObject $widget = null)
    {
        return [
            [
                "label" => $this->translation->getTranslatedString("RECIPES_SHOPPINGLIST"),
                "data" => $this->createList(),
                "value" => !is_null($widget) ? $widget->getOptions()["shoppinglist"] : null,
                "name" => "shoppinglist",
                "type" => "select"
            ]
        ];
    }

    public function getLink(WidgetObject $widget = null)
    {
        $id = $widget->getOptions()["shoppinglist"];
        return $this->router->urlFor('recipes_shoppinglists_view', ["shoppinglist" => $this->shoppinglists[$id]["hash"]]);
    }
}
