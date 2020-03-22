<?php

namespace App\Domain\Activity;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;

class Controller {

    protected $logger;
    protected $twig;
    protected $settings;
    protected $translation;
    protected $current_user;
    private $mapper;
    private $user_mapper;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Settings $settings,
            Translator $translation,
            CurrentUser $current_user,
            Mapper $mapper,
            \App\Domain\User\Mapper $user_mapper) {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->settings = $settings;
        $this->translation = $translation;
        $this->current_user = $current_user;

        $this->user_mapper = $user_mapper;
        $this->mapper = $mapper;
    }

    public function addEntry($type, $module, $controller, $object = [], $parent = [], $users = []) {
        $data = [];
        $data["user"] = $this->current_user->getUser()->id;
        $data["type"] = $type;
        $data["module"] = $module;
        $data["controller"] = $controller;
        $data["object"] = array_key_exists("object", $object) ? $object["object"] : null;
        $data["object_id"] = array_key_exists("id", $object) ? $object["id"] : null;
        $data["object_description"] = array_key_exists("description", $object) ? $object["description"] : null;
        $data["parent_object"] = array_key_exists("object", $parent) ? $parent["object"] : null;
        $data["parent_object_id"] = array_key_exists("id", $parent) ? $parent["id"] : null;
        $data["parent_object_description"] = array_key_exists("description", $parent) ? $parent["description"] : null;
        $data["link"] = array_key_exists("link", $object) ? $object["link"] : null;

        $activity = new Activity($data);
        $id = $this->mapper->insert($activity);

        $this->mapper->addUsers($id, $users);
    }

    public function index(Request $request, Response $response) {
        return $this->twig->render($response, 'activity/list.twig', []);
    }

    public function getActivities(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $response_data = ["data" => [], "status" => "success"];
        $count = array_key_exists('count', $data) ? filter_var($data['count'], FILTER_SANITIZE_NUMBER_INT) : 20;
        $offset = array_key_exists('start', $data) ? filter_var($data['start'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $limit = sprintf("%s,%s", $offset, $count);

        $user_id = $this->current_user->getUser()->id;
                
        $activities = $this->mapper->getUserItems("createdOn DESC", $limit, $user_id);

        $response_data["data"] = $this->renderTableRows($activities);
        $response_data["count"] = $this->mapper->getCountElementsOfUser($user_id);

        return $response->withJson($response_data);
    }

    private function renderTableRows($list) {

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

        $fmtDate = new \IntlDateFormatter($language, NULL, NULL);
        $fmtDate->setPattern($dateFormatPHP["date"]);

        $fmtTime = new \IntlDateFormatter($language, NULL, NULL);
        $fmtTime->setPattern($dateFormatPHP["time"]);

        $modules = $this->settings->getAppSettings()['modules'];

        $users = $this->user_mapper->getAll();
        $me = $this->current_user->getUser()->id;

        $rendered_data = [];
        foreach ($list as $el) {

            $action = "";
            switch ($el->type) {
                case 'create':
                    $action = "ACTIVITY_CREATE";
                    break;
                case 'update':
                    $action = "ACTIVITY_UPDATE";
                    break;
                case 'delete':
                    $action = "ACTIVITY_DELETE";
                    break;
            }


            if ($el->user !== $me) {
                $user = $users[$el->user]->name;
                $description = sprintf($this->translation->getTranslatedString($action), $user, $el->object_description);
            } else {
                $action = $action . "_ME";
                $description = sprintf($this->translation->getTranslatedString($action), $el->object_description);
            }


            if ($el->parent_object && $el->parent_object_description) {
                $parent_object = $this->translation->getTranslatedString($el->parent_object::$NAME);
                $description .= sprintf(" (%s: %s)", $parent_object, trim($el->parent_object_description));
            }

            $row = [];
            $row["date"] = $fmtDate->format(new \Datetime($el->createdOn));
            $row["time"] = $fmtTime->format(new \Datetime($el->createdOn));
            $row["icon"] = array_key_exists($el->module, $modules) ? $modules[$el->module]['icon'] : "fas fa-toolbox";
            $row["description"] = $description;
            $row["link"] = $el->type !== 'delete' ? $el->link : null;

            $rendered_data[] = $row;
        }

        return $rendered_data;
    }

}
