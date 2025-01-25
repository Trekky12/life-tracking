<?php

namespace App\Domain\Home\Widget;

use App\Domain\Main\Translator;
use App\Domain\Board\BoardService;
use App\Domain\Board\Card\CardMapper;
use Slim\Routing\RouteParser;

class BoardsCardsWidget implements Widget {

    private $translation;
    private $router;
    private $board_service;
    private $card_mapper;
    private $boards;

    public function __construct(
        Translator $translation,
        RouteParser $router,
        BoardService $board_service,
        CardMapper $card_mapper
    ) {
        $this->translation = $translation;
        $this->router = $router;
        $this->board_service = $board_service;
        $this->card_mapper = $card_mapper;

        $this->boards = $this->createList();
    }

    private function createList() {
        $boards = $this->board_service->getAllOrderedByName();

        $result = [];
        foreach ($boards as $board) {
            $result[$board->id] = ["name" => $board->name, "hash" => $board->getHash(), "url" => $this->router->urlFor('boards_stacks', ['hash' => $board->getHash()])];
        }

        return $result;
    }

    public function getListItems() {
        return array_keys($this->boards);
    }

    public function getContent(?WidgetObject $widget = null) {
        $stack_id = $widget->getOptions()["stack"];
        $due = $widget->getOptions()["card_type"] == "due";

        $cards = $this->card_mapper->getCardsWidget($stack_id, $due);

        return $cards;
    }

    public function getTitle(?WidgetObject $widget = null) {
        $id = $widget->getOptions()["board"];

        return sprintf("%s", $this->boards[$id]["name"]);
    }

    public function getOptions(?WidgetObject $widget = null) {
        return [
            [
                "label" => $this->translation->getTranslatedString("BOARD"),
                "data" => $this->createList(),
                "value" => !is_null($widget) ? $widget->getOptions()["board"] : null,
                "name" => "board",
                "type" => "select"
            ],
            [
                "label" => $this->translation->getTranslatedString("STACK"),
                "data" => [],
                "value" => !is_null($widget) ? $widget->getOptions()["stack"] : null,
                "name" => "stack",
                "type" => "select",
                "dependency" => "board"
            ],
            [
                "label" => $this->translation->getTranslatedString("CARD"),
                "data" => ["all" => ["name" => $this->translation->getTranslatedString("CARDS_ALL")], "due" => ["name" => $this->translation->getTranslatedString("CARDS_DUE")]],
                "value" => !is_null($widget) ? $widget->getOptions()["card_type"] : null,
                "name" => "card_type",
                "type" => "select"
            ],
        ];
    }

    public function getLink(?WidgetObject $widget = null) {
        $id = $widget->getOptions()["board"];
        return $this->router->urlFor('boards_view', ["hash" => $this->boards[$id]["hash"]]);
    }
}
