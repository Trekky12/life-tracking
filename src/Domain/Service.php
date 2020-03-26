<?php

namespace App\Domain;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use Hashids\Hashids;
use App\Application\Payload\Payload;

abstract class Service {

    protected $dataobject = \App\Domain\DataObject::class;
    // activities
    protected $create_activity = true;
    protected $module = "general";
    protected $dataobject_parent = null;
    protected $parent_object_service = null;
    protected $element_view_route = '';
    protected $element_view_route_params = [];
    // classes
    protected $translation;
    protected $settings;
    protected $activity;
    protected $router;
    protected $logger;
    protected $current_user;

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user) {
        $this->translation = $translation;
        $this->settings = $settings;
        $this->activity = $activity;
        $this->router = $router;
        $this->logger = $logger;
        $this->current_user = $user;
    }

    public function getEntry($entry_id, $user = null) {
        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);

            if ($this->mapper->hasUserTable()) {
                $entry_users = $this->mapper->getUsers($entry_id);
                $entry->setUsers($entry_users);
            }
        }

        return $entry;
    }

    public function createEntry($data, $user = null) {
        // Remove CSRF attributes
        if (array_key_exists('csrf_name', $data)) {
            unset($data["csrf_name"]);
        }
        if (array_key_exists('csrf_value', $data)) {
            unset($data["csrf_value"]);
        }

        if (!isset($user)) {
            $data['user'] = $this->current_user->getUser()->id;
        } else {
            $data['user'] = $user;
            // use this user for filtering
            $this->mapper->setUser($user);
        }

        $entry = new $this->dataobject($data);

        return $entry;
    }

    public function insertEntry($entry) {
        $id = $this->mapper->insert($entry);

        $this->addUsers($id, $entry);

        if ($this->create_activity) {
            $this->addActivity("create", $id);
        }

        return $id;
    }

    public function updateEntry($entry) {
        // try to access entry, maybe the user is not authorized, so this throws an exception (not found)
        $oldEntry = $this->mapper->get($entry->id);

        $elements_changed = $this->mapper->update($entry);

        $updated = $elements_changed > 0;

        if ($updated) {
            $this->addUsers($entry->id, $entry);
        }
        if ($this->create_activity) {
            $this->addActivity("update", $entry->id);
        }

        return $updated;
    }

    /**
     * Save m-n user table 
     */
    private function addUsers($id, $entry) {
        if ($this->mapper->hasUserTable()) {
            $this->mapper->deleteUsers($id);

            if (!empty($entry->getUsers())) {
                $this->mapper->addUsers($id, $entry->getUsers());
            }
        }
    }

    public function deleteEntry($id, $user) {

        if (isset($user)) {
            // use this user for filtering
            $this->mapper->setUser($user);
        }

        /**
         * get affected users
         */
        $savedEntry = $this->mapper->get($id);
        $affectedUsers = $this->getAffectedUsers($savedEntry);

        /**
         * Delete
         */
        $is_deleted = $this->mapper->delete($id);

        if ($is_deleted && $this->create_activity) {
            $this->addActivity("delete", $id, $savedEntry, $affectedUsers);
        }

        return $is_deleted;
    }

    public function isOwner($id) {
        $user = $this->current_user->getUser()->id;
        if (!is_null($id)) {
            $element = $this->mapper->get($id);

            if ($element->user !== $user) {
                return false;
            }
            return true;
        }
        return null;
    }

    /**
     * Users with access to a specific dataset
     */
    protected function getAffectedUsers($entry) {
        if ($this->hasParent()) {
            return $this->getParentObjectService()->getUsers($entry->getParentID());
        }
        return $this->getUsers($entry->id);
    }

    public function getUsers($id) {
        return $this->mapper->getUsers($id);
    }

    public function isMember($id) {
        $group_users = $this->getUsers($id);
        $user = $this->current_user->getUser()->id;
        if (!in_array($user, $group_users)) {
            return false;
        }
        return true;
    }

    protected function getElementViewRoute($entry) {
        if (empty($this->element_view_route)) {
            return null;
        }
        $this->element_view_route_params["id"] = $entry->id;

        return $this->router->urlFor($this->element_view_route, $this->element_view_route_params);
    }

    private function addActivity($activity_type, $id, $savedEntry = null, $affectedUsers = null) {
        try {
            $entry = $savedEntry ? $savedEntry : $this->mapper->get($id);
            $users = $affectedUsers ? $affectedUsers : $this->getAffectedUsers($entry);

            $object = ["object" => $this->dataobject, "id" => $id, "description" => $entry->getDescription($this->translation, $this->settings), "link" => $this->getElementViewRoute($entry)];
            $parent = ["object" => $this->dataobject_parent, "id" => $entry->getParentID(), "description" => $this->getParentDescription($entry)];

            $this->activity->addEntry($activity_type, $this->module, static::class, $object, $parent, $users);
        } catch (\Exception $e) {
            $this->logger->addWarning("Could not create activity entry", array("type" => $activity_type, "id" => $id, "error" => $e->getMessage()));
        }
    }

    protected function getParentObjectService() {
        return $this->parent_object_service;
    }

    function setParentObjectService($parent_object_service) {
        $this->parent_object_service = $parent_object_service;
    }

    private function getParentDescription($entry) {
        if ($this->hasParent()) {
            $parent_object = $this->getParentObjectService()->getEntry($entry->getParentID());
            return $parent_object->getDescription($this->translation, $this->settings);
        }
        return null;
    }

    public function hasParent() {
        return !is_null($this->dataobject_parent) && !is_null($this->getParentObjectService());
    }

    public function getDataObject() {
        return $this->dataobject;
    }

    public function getFromHash($hash) {
        return $this->mapper->getFromHash($hash);
    }

    public function setHash($id) {
        $entry = $this->mapper->get($id);
        if (empty($entry->getHash())) {
            $hashids = new Hashids('', 10);
            $hash = $hashids->encode($id);
            $this->mapper->setHash($id, $hash);
        }
    }

}
