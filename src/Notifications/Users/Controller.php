<?php

namespace App\Notifications\Users;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {
    
    protected $module = "notifications";

    public function init() {
        $this->mapper = new Mapper($this->ci);
    }

    public function getCategoriesByUser() {
        $user = $this->ci->get('helper')->getUser();
        return $this->mapper->getCategoriesByUser($user->id);
    }

    public function setCategoryforUser(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $result = ["status" => "success"];
        $category = array_key_exists('category', $data) ? intval(filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $type = array_key_exists('type', $data) ? intval(filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $user = $this->ci->get('helper')->getUser();

        if ($type == 1) {
            $this->mapper->addCategory($user->id, $category);
        } else {
            $this->mapper->deleteCategory($user->id, $category);
        }

        return $response->withJson($result);
    }

}
