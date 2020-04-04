<?php

namespace App\Domain\Activity;

use App\Domain\Base\CurrentUser;
use App\Domain\Main\Translator;
use App\Domain\Base\Settings;
use Slim\Routing\RouteParser;

class ActivityCreator {

    private $mapper;
    private $current_user;
    private $translation;
    private $settings;
    private $router;

    public function __construct(CurrentUser $current_user, Translator $translation, Settings $settings, RouteParser $router, ActivityMapper $mapper) {
        $this->current_user = $current_user;
        $this->translation = $translation;
        $this->settings = $settings;
        $this->router = $router;
        $this->mapper = $mapper;
    }

    public function saveActivity(Activity $activity) {
        $id = $this->mapper->insert($activity);
        $this->mapper->addUsers($id, $activity->getUsers());
    }

    public function createActivity($activity_type, $module, $id, $mapper, $link, $parent_mapper = null, $parent_id = null): Activity {
        $entry = $mapper->get($id);
        $users = $mapper->getUsers($id);

        $entry_link = $this->router->urlFor($link['route'], $link['params']);

        $object = ["object" => $mapper->getDataObject(), "id" => $id, "description" => $entry->getDescription($this->translation, $this->settings), "link" => $entry_link];
        $parent = [];

        if (isset($parent_mapper) && isset($parent_id)) {
            $parent_entry = $parent_mapper->get($parent_id);
            $users = $parent_mapper->getUsers($parent_id);
            $parent = ["object" => $parent_mapper->getDataObject(), "id" => $parent_id, "description" => $parent_entry->getDescription($this->translation, $this->settings)];
        }

        return $this->createActivityEntry($activity_type, $module, $object, $parent, $users);
    }

    private function createActivityEntry($type, $module, $object = [], $parent = [], $users = []): Activity {
        $data = [];
        $data["user"] = $this->current_user->getUser()->id;
        $data["type"] = $type;
        $data["module"] = $module;
        $data["controller"] = null;
        $data["object"] = array_key_exists("object", $object) ? $object["object"] : null;
        $data["object_id"] = array_key_exists("id", $object) ? $object["id"] : null;
        $data["object_description"] = array_key_exists("description", $object) ? $object["description"] : null;
        $data["parent_object"] = array_key_exists("object", $parent) ? $parent["object"] : null;
        $data["parent_object_id"] = array_key_exists("id", $parent) ? $parent["id"] : null;
        $data["parent_object_description"] = array_key_exists("description", $parent) ? $parent["description"] : null;
        $data["link"] = array_key_exists("link", $object) ? $object["link"] : null;

        $activity = new Activity($data);
        $activity->setUsers($users);

        return $activity;
    }

}
