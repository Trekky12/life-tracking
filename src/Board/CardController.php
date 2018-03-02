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
        $user_stacks = $this->board_mapper->getUserStacks($user);

        if (!array_key_exists("stack", $data) || !in_array($data["stack"], $user_stacks)) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
        }
    }

    public function updatePosition(Request $request, Response $response) {
        $data = $request->getParsedBody();

        if (array_key_exists("card", $data) && !empty($data["card"])) {
            try {
                foreach ($data['card'] as $position => $item) {
                    $this->mapper->updatePosition($item, $position);
                }
            } catch (\Exception $e) {
                return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
            }
        }
        return $response->withJSON(array('status' => 'success'));
    }

}
