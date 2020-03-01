<?php

namespace App\Notifications\Clients;

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

    protected $model = '\App\Notifications\Clients\NotificationClient';
    protected $index_route = 'notifications';
    protected $module = "notifications";

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation, CurrentUser $current_user) {
        parent::__construct($logger, $twig, $helper, $flash, $router, $settings, $db, $activity, $translation, $current_user);


        $this->mapper = new Mapper($this->db, $this->translation, $current_user);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getAll();
        $users = $this->user_mapper->getAll();
        return $this->twig->render($response, 'notifications/clients/index.twig', ['list' => $list, 'users' => $users]);
    }

    public function subscribe(Request $request, Response $response) {

        //$data = json_decode($request->getBody(), true);
        $data = $request->getParsedBody();

        $entry = new NotificationClient($data);
        $entry->ip = $this->helper->getIP();
        $entry->agent = $this->helper->getAgent();
        $entry->user = $this->current_user->getUser()->id;
        $entry->changedOn = date('Y-m-d H:i:s');

        $result = array('status' => 'error');

        if ($request->isPost()) {
            $this->logger->addInfo('Subscription insert', $entry->get_fields());
            $this->mapper->insert($entry);
            $result['status'] = 'success';
        }
        if ($request->isPut()) {
            $entry->changedOn = date('Y-m-d H:i:s');

            try {
                $this->mapper->get($entry->endpoint, true, 'endpoint');
                $this->mapper->update($entry, "endpoint");
                $result['status'] = 'success';
            } catch (\Exception $e) {
                // No Entry found so create one
                //$this->logger->addWarning('Subscription not on server but on client', $entry->get_fields());
                //$this->mapper->insert($entry);
            }
        }
        if ($request->isDelete()) {
            $this->logger->addInfo('Subscription delete', $entry->get_fields());
            $this->mapper->delete($entry->endpoint, "endpoint");
            $result['status'] = 'success';
        }

        return $response->withJSON($result);
    }

    public function getCategoriesFromEndpoint(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $result = ["data" => [], "status" => "success"];
        $endpoint = array_key_exists('endpoint', $data) ? filter_var($data['endpoint'], FILTER_SANITIZE_STRING) : null;
        $result["data"] = $this->mapper->getCategoriesByEndpoint($endpoint);

        return $response->withJson($result);
    }

    public function setCategoryOfEndpoint(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $result = ["status" => "success"];
        $endpoint = array_key_exists('endpoint', $data) ? filter_var($data['endpoint'], FILTER_SANITIZE_STRING) : null;
        $category = array_key_exists('category', $data) ? intval(filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $type = array_key_exists('type', $data) ? intval(filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $client = $this->mapper->getClientByEndpoint($endpoint);
        if ($type == 1) {
            $this->mapper->addCategory($client->id, $category);
        } else {
            $this->mapper->deleteCategory($client->id, $category);
        }

        return $response->withJson($result);
    }

}
