<?php

namespace App\Board;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    
    public function init() {
        $this->model = '\App\Board\Board';
        $this->index_route = 'boards';
        $this->edit_template = 'boards/edit.twig';
        
        $this->mapper = new \App\Board\Mapper($this->ci );
        $this->mapper->setUserTable("boards_user", "board");
        
        $this->user_mapper = new \App\User\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $boards = $this->mapper->getAll('name');
        return $this->ci->view->render($response, 'boards/index.twig', ['boards' => $boards]);
    }
    
    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
            $board_user = (array) $this->mapper->getUsers($entry_id, "boards_user", "board");
            $entry->setUsers($board_user);
        }

        $users = $this->user_mapper->getAll('name');

        return $this->ci->view->render($response, 'boards/edit.twig', ['entry' => $entry, "users" => $users]);
    }
    


}
