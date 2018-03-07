<?php

namespace App\Board;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class BoardController extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Board\Board';
        $this->index_route = 'boards';
        $this->edit_template = 'boards/edit.twig';

        $this->mapper = new \App\Board\BoardMapper($this->ci);
        $this->user_mapper = new \App\User\Mapper($this->ci);
        $this->stack_mapper = new \App\Board\StackMapper($this->ci);
        $this->card_mapper = new \App\Board\CardMapper($this->ci);
        $this->label_mapper = new \App\Board\LabelMapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $boards = $this->mapper->getVisibleBoards('name');
        return $this->ci->view->render($response, 'boards/index.twig', ['boards' => $boards]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
            $board_user = $this->mapper->getUsers($entry_id);
            $entry->setUsers($board_user);
        }

        $users = $this->user_mapper->getAll('name');

        $this->preEdit($entry_id);

        return $this->ci->view->render($response, 'boards/edit.twig', ['entry' => $entry, "users" => $users]);
    }

    public function view(Request $request, Response $response) {
        $hash = $request->getAttribute('hash');

        $board = $this->mapper->getBoardFromHash($hash);

        /**
         * Is the user allowed to view this board?
         */
        $board_user = $this->mapper->getUsers($board->id);
        $user = $this->ci->get('helper')->getUser()->id;
        if (!in_array($user, $board_user)) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
        }

        /**
         * Get stacks with cards
         */
        $stacks = $this->stack_mapper->getStacksFromBoard($board->id);

        foreach ($stacks as &$stack) {
            $stack->cards = $this->card_mapper->getCardsFromStack($stack->id);
        }


        $users = $this->user_mapper->getAll('name');

        $card_user = $this->card_mapper->getCardsUser();

        $labels = $this->label_mapper->getLabelsFromBoard($board->id);

        return $this->ci->view->render($response, 'boards/view.twig', [
                    'board' => $board,
                    'stacks' => $stacks,
                    "users" => $users,
                    "card_user" => $card_user,
                    "labels" => $labels
        ]);
    }

}
