<?php

namespace App\Notifications\Users;

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

    protected $module = "notifications";

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation, CurrentUser $current_user) {
        parent::__construct($logger, $twig, $helper, $flash, $router, $settings, $db, $activity, $translation, $current_user);


        $this->mapper = new Mapper($this->db, $this->translation, $current_user);
    }

    public function getCategoriesByUser() {
        $user = $this->current_user->getUser();
        return $this->mapper->getCategoriesByUser($user->id);
    }

    public function setCategoryforUser(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $result = ["status" => "success"];
        $category = array_key_exists('category', $data) ? intval(filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $type = array_key_exists('type', $data) ? intval(filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $user = $this->current_user->getUser();

        if ($type == 1) {
            $this->mapper->addCategory($user->id, $category);
        } else {
            $this->mapper->deleteCategory($user->id, $category);
        }

        return $response->withJson($result);
    }

}
