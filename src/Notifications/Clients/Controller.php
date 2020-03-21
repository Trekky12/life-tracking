<?php

namespace App\Notifications\Clients;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\User\UserService;

class Controller extends \App\Base\Controller {

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            NotificationClientsService $service,
            UserService $user_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->user_service = $user_service;
    }

    public function index(Request $request, Response $response) {
        $list = $this->service->getClients();
        $users = $this->user_service->getAll();
        return $this->twig->render($response, 'notifications/clients/index.twig', ['list' => $list, 'users' => $users]);
    }

    public function subscribe(Request $request, Response $response) {

        //$data = json_decode($request->getBody(), true);
        $data = $request->getParsedBody();

        $result = array('status' => 'error');

        if ($request->isPost()) {

            $entry = $this->service->createSubscription($data);
            $this->logger->addInfo('Subscription insert', $entry->get_fields());

            $result['status'] = 'success';
        }
        if ($request->isPut()) {
            $client = $this->service->updateSubscription($data);

            if ($client) {
                $result['status'] = 'success';
            }
        }
        if ($request->isDelete()) {
            $entry = $this->service->deleteSubscription($data);
            $this->logger->addInfo('Subscription delete', $entry->get_fields());
            $result['status'] = 'success';
        }

        return $response->withJSON($result);
    }

    public function getCategoriesFromEndpoint(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $result = ["data" => [], "status" => "success"];
        $endpoint = array_key_exists('endpoint', $data) ? filter_var($data['endpoint'], FILTER_SANITIZE_STRING) : null;
        $result["data"] = $this->service->getCategoriesFromEndpoint($endpoint);

        return $response->withJson($result);
    }

    public function setCategoryOfEndpoint(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $result = ["status" => "success"];
        $endpoint = array_key_exists('endpoint', $data) ? filter_var($data['endpoint'], FILTER_SANITIZE_STRING) : null;
        $category = array_key_exists('category', $data) ? intval(filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $type = array_key_exists('type', $data) ? intval(filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $this->service->setCategoryOfEndpoint($endpoint, $category, $type);

        return $response->withJson($result);
    }
    
    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $response_data = $this->doDelete($id);
        return $response->withJson($response_data);
    }

}
