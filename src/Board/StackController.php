<?php

namespace App\Board;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class StackController extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Board\Stack';

        $this->mapper = new \App\Board\StackMapper($this->ci);
        $this->board_mapper = new \App\Board\BoardMapper($this->ci);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, $data) {
        $user = $this->ci->get('helper')->getUser()->id;
        $user_boards = $this->board_mapper->getElementsOfUser($user);
        if (!array_key_exists("board", $data) || !in_array($data["board"], $user_boards)) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
        }
    }

    public function updatePosition(Request $request, Response $response) {
        $data = $request->getParsedBody();
        /**
         * Save new order
         * @see https://stackoverflow.com/a/15635201
         */
        if (array_key_exists("stack", $data) && !empty($data["stack"])) {
            try {
                foreach ($data['stack'] as $position => $item) {
                    $this->mapper->updatePosition($item, $position);
                }
            } catch (\Exception $e) {
                return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
            }
        }
        return $response->withJSON(array('status' => 'success'));
    }

}
