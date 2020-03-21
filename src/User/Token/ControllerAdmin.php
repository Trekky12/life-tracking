<?php

namespace App\User\Token;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\User\UserService;

class ControllerAdmin extends \App\Base\Controller {

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            TokenService $service,
            UserService $user_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->user_service = $user_service;
    }

    public function index(Request $request, Response $response) {
        $list = $this->service->getLoginTokens();
        $users = $this->user_service->getAll();
        return $this->twig->render($response, 'user/tokens.twig', ['list' => $list, 'users' => $users]);
    }

    public function deleteOld(Request $request, Response $response) {
        $this->service->deleteOldTokens();
        return $response->withRedirect($this->router->urlFor('login_tokens'), 301);
    }
    
    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $response_data = $this->doDelete($id);
        return $response->withJson($response_data);
    }

}
