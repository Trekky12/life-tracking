<?php

namespace App\Activity;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->mapper = new Mapper($this->ci);
    }

    public function addEntry($type, $module, $controller, $object = [], $parent= [], $users = []) {
        $data = [];
        $data["user"] = $this->ci->get('helper')->getUser()->id;
        $data["type"] = $type;
        $data["module"] = $module;
        $data["controller"] = $controller;
        $data["object"] = array_key_exists("object", $object)? $object["object"] : null;
        $data["object_id"] = array_key_exists("id", $object)? $object["id"] : null;
        $data["object_description"] = array_key_exists("description", $object)? $object["description"] : null;
        $data["parent_object"] = array_key_exists("object", $parent)? $parent["object"] : null;
        $data["parent_object_id"] = array_key_exists("id", $parent)? $parent["id"] : null;
        $data["parent_object_description"] = array_key_exists("description", $parent)? $parent["description"] : null;
        $data["link"] = array_key_exists("link", $object)? $object["link"] : null;

        $model = new $this->model($data);
        $id = $this->mapper->insert($model);

        $this->mapper->addUsers($id, $users);
    }

    public function index(Request $request, Response $response) {
        return $this->ci->get('view')->render($response, 'activity/list.twig', []);
    }

    public function getActivities(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $result = ["data" => [], "status" => "success"];
        $count = array_key_exists('count', $data) ? filter_var($data['count'], FILTER_SANITIZE_NUMBER_INT) : 20;
        $offset = array_key_exists('start', $data) ? filter_var($data['start'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $limit = sprintf("%s,%s", $offset, $count);

        $activities = $this->mapper->getUserItems("createdOn DESC", $limit);

        $result["data"] = $this->renderTableRows($activities);
        $result["count"] = $this->mapper->getCountElementsOfUser();

        return $response->withJson($result);
    }

    private function renderTableRows($list) {

        $langugage = $this->ci->get('settings')['app']['i18n']['php'];
        $dateFormatPHP = $this->ci->get('settings')['app']['i18n']['dateformatPHP'];

        $fmtDate = new \IntlDateFormatter($langugage, NULL, NULL);
        $fmtDate->setPattern($dateFormatPHP["date"]);
        
        $fmtTime = new \IntlDateFormatter($langugage, NULL, NULL);
        $fmtTime->setPattern($dateFormatPHP["time"]);

        $modules = $this->ci->get('settings')['app']['modules'];

        $users = $this->user_mapper->getAll();
        $me = $this->ci->get('helper')->getUser()->id;

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
                $description = sprintf($this->ci->get('helper')->getTranslatedString($action), $user, $el->object_description);
            } else {
                $action = $action . "_ME";
                $description = sprintf($this->ci->get('helper')->getTranslatedString($action), $el->object_description);
            }
            
                        
            if($el->parent_object && $el->parent_object_description){
                $parent_object = $this->ci->get('helper')->getTranslatedString($el->parent_object::$MODEL_NAME);
                $description .= sprintf(" (%s: %s)", $parent_object, trim($el->parent_object_description));
            }

            $row = [];
            $row["date"] = $fmtDate->format(new \Datetime($el->createdOn));
            $row["time"] = $fmtTime->format(new \Datetime($el->createdOn));
            $row["icon"] = array_key_exists($el->module, $modules) ? $modules[$el->module]['icon'] : "fas fa-toolbox";
            $row["description"] = $description;
            $row["link"] = $el->type !== 'delete'? $el->link: null;

            $rendered_data[] = $row;
        }

        return $rendered_data;
    }

}
