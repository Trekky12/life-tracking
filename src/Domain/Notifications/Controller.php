<?php

namespace App\Domain\Notifications;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Notifications\Categories\NotificationCategoryService;
use App\Domain\Notifications\Clients\NotificationClientsService;

class Controller extends \App\Domain\Base\Controller {

    private $cat_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            NotificationsService $service,
            NotificationCategoryService $cat_service,
            NotificationClientsService $client_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->cat_service = $cat_service;
        $this->client_service = $client_service;
    }

    public function manage(Request $request, Response $response) {
        $categories = $this->cat_service->getAllCategories();
        $user_categories = $this->service->getCategoriesOfCurrentUser();

        return $this->twig->render($response, 'notifications/manage.twig', ["categories" => $categories, "user_categories" => $user_categories]);
    }

    public function overview(Request $request, Response $response) {
        return $this->twig->render($response, 'notifications/overview.twig', []);
    }

    public function notifyByCategory(Request $request, Response $response) {

        $requestData = $request->getQueryParams();

        $category = array_key_exists("type", $requestData) ? filter_var($requestData["type"], FILTER_SANITIZE_STRING) : "";
        $title = array_key_exists("title", $requestData) ? filter_var($requestData["title"], FILTER_SANITIZE_STRING) : "";
        $message = array_key_exists("message", $requestData) ? filter_var($requestData["message"], FILTER_SANITIZE_STRING) : "";

        $this->service->sendNotificationsToUsersWithCategory($category, $title, $message);

        $response_data = ['status' => 'done'];
        return $response->withJson($response_data);
    }

    public function testNotification(Request $request, Response $response) {
        $entry_id = $request->getAttribute('id');

        $entry = $this->client_service->getEntry($entry_id);

        if ($request->isPost()) {

            $data = $request->getParsedBody();
            $title = array_key_exists('title', $data) ? filter_var($data['title'], FILTER_SANITIZE_STRING) : null;
            $message = array_key_exists('message', $data) ? filter_var($data['message'], FILTER_SANITIZE_STRING) : null;

            $result = $this->service->sendNotification($entry, $title, $message);

            if ($result) {
                $this->flash->addMessage('message', $this->translation->getTranslatedString("NOTIFICATION_SEND_SUCCESS"));
                $this->flash->addMessage('message_type', 'success');
            } else {
                $this->flash->addMessage('message', $this->translation->getTranslatedString("NOTIFICATION_SEND_FAILURE"));
                $this->flash->addMessage('message_type', 'danger');
            }
            return $response->withRedirect($this->router->urlFor('notifications_clients'), 301);
        }

        return $this->twig->render($response, 'notifications/clients/test.twig', ['entry' => $entry]);
    }

    public function getNotificationsByUser(Request $request, Response $response) {
        $data = $request->getParsedBody();

        //$endpoint = array_key_exists('endpoint', $data) ? filter_var($data['endpoint'], FILTER_SANITIZE_STRING) : null;
        $limit = array_key_exists('count', $data) ? filter_var($data['count'], FILTER_SANITIZE_NUMBER_INT) : 5;
        $offset = array_key_exists('start', $data) ? filter_var($data['start'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $result = $this->service->getNotifications($limit, $offset);

        return $response->withJson($result);
    }

}
