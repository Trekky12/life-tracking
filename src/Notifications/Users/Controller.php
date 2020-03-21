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

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            NotificationUsersService $service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
    }

    public function setCategoryforUser(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $result = ["status" => "success"];
        $category = array_key_exists('category', $data) ? intval(filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $type = array_key_exists('type', $data) ? intval(filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $this->service->setCategoryForUser($category, $type);

        return $response->withJson($result);
    }

}
