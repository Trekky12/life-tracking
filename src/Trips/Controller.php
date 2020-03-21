<?php

namespace App\Trips;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Trips\Event\TripEventService;
use App\User\UserService;

class Controller extends \App\Base\Controller {

    private $event_service;
    private $user_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            TripService $service,
            TripEventService $event_service,
            UserService $user_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->event_service = $event_service;
        $this->user_service = $user_service;
    }

    public function index(Request $request, Response $response) {
        $trips = $this->service->getUserTrips();
        $dates = $this->event_service->getMinMaxEventsDates();
        return $this->twig->render($response, 'trips/index.twig', ['trips' => $trips, 'dates' => $dates]);
    }

    public function edit(Request $request, Response $response) {
        $entry_id = $request->getAttribute('id');

        if ($this->service->isOwner($entry_id) === false) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $entry = $this->service->getEntry($entry_id);
        $users = $this->user_service->getAll();

        return $this->twig->render($response, 'trips/edit.twig', ['entry' => $entry, 'users' => $users]);
    }
    
    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        
        if ($this->service->isOwner($id) === false) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $new_id = $this->doSave($id, $data, null);
        
        $this->service->setHash($new_id);

        $redirect_url = $this->router->urlFor('trips');
        return $response->withRedirect($redirect_url, 301);
    }
    
    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');

        if ($this->service->isOwner($id) === false) {
            $response_data = ['is_deleted' => false, 'error' => $this->translation->getTranslatedString('NO_ACCESS')];
        } else {
            $response_data = $this->doDelete($id);
        }
        return $response->withJson($response_data);
    }

}
