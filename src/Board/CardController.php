<?php

namespace App\Board;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CardController extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Board\Card';

        $this->mapper = new \App\Board\CardMapper($this->ci);
        $this->board_mapper = new \App\Board\BoardMapper($this->ci);
        $this->stack_mapper = new \App\Board\StackMapper($this->ci);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, $data) {
        $user = $this->ci->get('helper')->getUser()->id;

        if (!is_null($id)) {
            $user_cards = $this->board_mapper->getUserCards($user);
            if (!in_array($id, $user_cards)) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        } elseif (is_array($data)) {
            $user_stacks = $this->board_mapper->getUserStacks($user);
            if (!array_key_exists("stack", $data) || !in_array($data["stack"], $user_stacks)) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

    public function updatePosition(Request $request, Response $response) {
        $data = $request->getParsedBody();

        try {

            $user = $this->ci->get('helper')->getUser()->id;
            $user_cards = $this->board_mapper->getUserCards($user);

            if (array_key_exists("card", $data) && !empty($data["card"])) {

                foreach ($data['card'] as $position => $item) {
                    if (in_array($item, $user_cards)) {
                        $this->mapper->updatePosition($item, $position);
                    }
                }
                return $response->withJSON(array('status' => 'success'));
            }
        } catch (\Exception $e) {
            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }
        return $response->withJSON(array('status' => 'error'));
    }

    public function moveCard(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $stack = array_key_exists("stack", $data) && !empty($data["stack"]) ? filter_var($data['stack'], FILTER_SANITIZE_NUMBER_INT) : null;
        $card = array_key_exists("card", $data) && !empty($data["card"]) ? filter_var($data['card'], FILTER_SANITIZE_NUMBER_INT) : null;

        try {
            $user = $this->ci->get('helper')->getUser()->id;
            $user_cards = $this->board_mapper->getUserCards($user);
            $user_stacks = $this->board_mapper->getUserStacks($user);

            if (!is_null($stack) && !is_null($card) && in_array($stack, $user_stacks) && in_array($card, $user_cards)) {
                $this->mapper->moveCard($card, $stack);
                return $response->withJSON(array('status' => 'success'));
            }
        } catch (\Exception $e) {
            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }
        return $response->withJSON(array('status' => 'error'));
    }

    public function archive(Request $request, Response $response) {
        try {
            $id = $request->getAttribute('id');

            $this->preSave($id, null);

            $is_archived = $this->mapper->setArchive($id, 1);
            $newResponse = $response->withJson(['is_archived' => $is_archived]);
            return $newResponse;
        } catch (\Exception $e) {
            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }
    }

}
