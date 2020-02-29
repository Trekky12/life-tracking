<?php

namespace App\Notifications\Users;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;

class Controller extends \App\Base\Controller {
    
    protected $module = "notifications";

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        
        $this->mapper = new Mapper($this->db, $this->translation, $user);
    }

    public function getCategoriesByUser() {
        $user = $this->user_helper->getUser();
        return $this->mapper->getCategoriesByUser($user->id);
    }

    public function setCategoryforUser(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $result = ["status" => "success"];
        $category = array_key_exists('category', $data) ? intval(filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $type = array_key_exists('type', $data) ? intval(filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $user = $this->user_helper->getUser();

        if ($type == 1) {
            $this->mapper->addCategory($user->id, $category);
        } else {
            $this->mapper->deleteCategory($user->id, $category);
        }

        return $response->withJson($result);
    }

}
