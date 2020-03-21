<?php

namespace App\Car;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\User\UserService;

class Controller extends \App\Base\Controller {

    private $user_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            CarService $service,
            UserService $user_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->user_service = $user_service;
    }

    public function index(Request $request, Response $response) {
        $cars = $this->service->getAllCarsOrderedByName();
        return $this->twig->render($response, 'cars/control/index.twig', ['cars' => $cars]);
    }

    public function edit(Request $request, Response $response) {
        $entry_id = $request->getAttribute('id');
        $entry = $this->service->getEntry($entry_id);
        $users = $this->user_service->getAll();

        return $this->twig->render($response, 'cars/control/edit.twig', ['entry' => $entry, 'users' => $users]);
    }
    
    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        $new_id = $this->doSave($id, $data, null);

        $redirect_url = $this->router->urlFor('cars');
        return $response->withRedirect($redirect_url, 301);
    }
    
    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $response_data = $this->doDelete($id);
        return $response->withJson($response_data);
    }

}
