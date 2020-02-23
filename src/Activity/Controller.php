<?php

namespace App\Activity;

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;

class Controller {

    protected $twig;
    protected $user_helper;
    protected $settings;
    protected $translation;

    public function __construct(ContainerInterface $ci) {
        $this->twig = $ci->get('view');
        $this->user_helper = $ci->get('user_helper');
        $this->settings = $ci->get('settings');
        $this->translation = $ci->get('translation');
        $this->db = $ci->get('db');

        $user = $this->user_helper->getUser();
        $this->user_mapper = new \App\User\Mapper($this->db, $this->translation);
        $this->mapper = new Mapper($this->db, $this->translation, $user);
    }

    public function addEntry($type, $module, $controller, $object = [], $parent = [], $users = []) {
        $data = [];
        $data["user"] = $this->user_helper->getUser()->id;
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
        
        $model = new Activity($data);
        $id = $this->mapper->insert($model);

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

        $activities = $this->mapper->getUserItems("createdOn DESC", $limit);

        $response_data["data"] = $this->renderTableRows($activities);
        $response_data["count"] = $this->mapper->getCountElementsOfUser();

        return $response->withJson($response_data);
    }

    private function renderTableRows($list) {

        $language = $this->settings['app']['i18n']['php'];
        $dateFormatPHP = $this->settings['app']['i18n']['dateformatPHP'];

        $fmtDate = new \IntlDateFormatter($language, NULL, NULL);
        $fmtDate->setPattern($dateFormatPHP["date"]);

        $fmtTime = new \IntlDateFormatter($language, NULL, NULL);
        $fmtTime->setPattern($dateFormatPHP["time"]);

        $modules = $this->settings['app']['modules'];

        $users = $this->user_mapper->getAll();
        $me = $this->user_helper->getUser()->id;

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
                $parent_object = $this->translation->getTranslatedString($el->parent_object::$MODEL_NAME);
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
