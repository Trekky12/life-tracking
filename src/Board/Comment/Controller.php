<?php

namespace App\Board\Comment;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Main\Helper;
use App\Activity\Controller as Activity;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Board\Comment\Comment';
    protected $parent_model = '\App\Board\Card\Card';
    protected $element_view_route = 'boards_view';
    protected $module = "boards";
    private $card_mapper;
    private $stack_mapper;
    private $board_mapper;

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation, CurrentUser $current_user) {
        parent::__construct($logger, $twig, $helper, $flash, $router, $settings, $db, $activity, $translation, $current_user);

        $this->mapper = new Mapper($this->db, $this->translation, $current_user);
        $this->card_mapper = new \App\Card\Mapper($this->db, $this->translation, $current_user);
        $this->stack_mapper = new \App\Stack\Mapper($this->db, $this->translation, $current_user);
        $this->board_mapper = new \App\Board\Mapper($this->db, $this->translation, $current_user);
    }

    /**
     * Does the user have access to this card?
     */
    protected function preSave($id, array $data, Request $request) {
        $user = $this->current_user->getUser()->id;
        $user_cards = $this->board_mapper->getUserCards($user);

        if (!is_null($id)) {
            $comment = $this->mapper->get($id);
            if (!in_array($comment->card, $user_cards)) {
                throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
            }
        } elseif (is_array($data)) {
            if (!array_key_exists("card", $data) || !in_array($data["card"], $user_cards)) {
                throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

    protected function getElementViewRoute($entry) {
        $card = $this->card_mapper->get($entry->getParentID());
        $stack = $this->stack_mapper->get($card->getParentID());
        $board = $this->board_mapper->get($stack->getParentID());
        $this->element_view_route_params["hash"] = $board->getHash();
        return parent::getElementViewRoute($entry);
    }

    protected function getParentObjectMapper() {
        return $this->card_mapper;
    }

}
