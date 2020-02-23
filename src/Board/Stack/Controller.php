<?php

namespace App\Board\Stack;

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Board\Stack\Stack';
    protected $parent_model = '\App\Board\Board';
    protected $element_view_route = 'boards_view';
    protected $module = "boards";
    private $board_mapper;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        
        $this->mapper = new Mapper($this->db, $this->translation, $user);
        $this->board_mapper = new \App\Board\Mapper($this->db, $this->translation, $user);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, array &$data, Request $request) {
        $user = $this->user_helper->getUser()->id;

        if (!is_null($id)) {
            $user_stacks = $this->board_mapper->getUserStacks($user);
            if (!in_array($id, $user_stacks)) {
                throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
            }
        } elseif (is_array($data)) {
            $user_boards = $this->board_mapper->getElementsOfUser($user);
            if (!array_key_exists("board", $data) || !in_array($data["board"], $user_boards)) {
                throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

    public function updatePosition(Request $request, Response $response) {
        $data = $request->getParsedBody();

        try {
            $user = $this->user_helper->getUser()->id;
            $user_stacks = $this->board_mapper->getUserStacks($user);
            /**
             * Save new order
             * @see https://stackoverflow.com/a/15635201
             */
            if (array_key_exists("stack", $data) && !empty($data["stack"])) {
                foreach ($data['stack'] as $position => $item) {
                    if (in_array($item, $user_stacks)) {
                        $this->mapper->updatePosition($item, $position, $user);
                    }
                }
                $response_data = ['status' => 'success'];
                return $response->withJSON($response_data);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Update Stack Position", array("data" => $data, "error" => $e->getMessage()));

            $response_data = ['status' => 'error', "error" => $e->getMessage()];
            return $response->withJSON($response_data);
        }
        $response_data = ['status' => 'error'];
        return $response->withJSON($response_data);
    }

    public function archive(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $id = $request->getAttribute('id');
        try {
            $data1 = [];
            $this->preSave($id, $data1, $request);

            if (array_key_exists("archive", $data) && in_array($data["archive"], array(0, 1))) {

                $user = $this->user_helper->getUser()->id;
                $is_archived = $this->mapper->setArchive($id, $data["archive"], $user);

                $response_data = ['is_archived' => $is_archived];
                return $response->withJson($response_data);
            } else {
                $response_data = ['status' => 'error', "error" => "missing data"];
                return $response->withJSON($response_data);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Archive Stack", array("data" => $data, "id" => $id, "error" => $e->getMessage()));

            $response_data = ['status' => 'error', "error" => $e->getMessage()];
            return $response->withJSON($response_data);
        }
    }

    protected function afterGetAPI($id, $entry, Request $request) {

        if ($entry->name) {
            $entry->name = htmlspecialchars_decode($entry->name);
        }
        return $entry;
    }

    protected function preDelete($id, Request $request) {
        $user = $this->user_helper->getUser()->id;
        $user_stacks = $this->board_mapper->getUserStacks($user);
        if (!in_array($id, $user_stacks)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }
    }

    protected function getElementViewRoute($entry) {
        $board = $this->getParentObjectMapper()->get($entry->getParentID());
        $this->element_view_route_params["hash"] = $board->getHash();
        return parent::getElementViewRoute($entry);
    }

    protected function getParentObjectMapper() {
        return $this->board_mapper;
    }

}
