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

        if (!is_null($id)) {
            $user_stacks = $this->board_mapper->getUserStacks($user);
            if (!in_array($id, $user_stacks)) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        } elseif (is_array($data)) {
            $user_boards = $this->board_mapper->getElementsOfUser($user);
            if (!array_key_exists("board", $data) || !in_array($data["board"], $user_boards)) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

    public function updatePosition(Request $request, Response $response) {
        $data = $request->getParsedBody();

        try {
            $user = $this->ci->get('helper')->getUser()->id;
            $user_stacks = $this->board_mapper->getUserStacks($user);
            /**
             * Save new order
             * @see https://stackoverflow.com/a/15635201
             */
            if (array_key_exists("stack", $data) && !empty($data["stack"])) {
                foreach ($data['stack'] as $position => $item) {
                    if (in_array($item, $user_stacks)) {
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

    public function archive(Request $request, Response $response) {
        $data = $request->getParsedBody();
        try {
            $id = $request->getAttribute('id');

            $this->preSave($id, null);

            if (array_key_exists("archive", $data) && in_array($data["archive"], array(0, 1))) {

                $is_archived = $this->mapper->setArchive($id, $data["archive"]);
                $newResponse = $response->withJson(['is_archived' => $is_archived]);
                return $newResponse;
            } else {
                return $response->withJSON(array('status' => 'error', "error" => "missing data"));
            }
        } catch (\Exception $e) {
            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }
    }

}
