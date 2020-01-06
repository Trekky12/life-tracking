<?php

namespace App\Base;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

abstract class Controller {

    protected $ci;
    protected $mapper;
    protected $model = '\App\Base\Model';
    protected $parent_model = null;
    protected $user_mapper;
    // Redirect the user to the index after saving
    protected $index_route = '';
    protected $index_params = [];
    protected $edit_template = '';
    protected $element_view_route = '';
    protected $element_view_route_params = [];
    // use user id from attribute instead of the current user (save/delete)
    protected $user_from_attribute = false;
    // logger
    protected $logger;
    // activities
    protected $create_activity = true;
    // module of the current controller
    protected $module = "general";

    final public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
        $this->user_mapper = new \App\User\Mapper($this->ci);
        $this->logger = $this->ci->get('logger');
        $this->init();
    }

    /**
     * Initialize the database mappers
     */
    abstract function init();

    /**
     * this function is called after successfully saving an entry
     * @param int $id
     * @param array $data
     * @param Request $request
     */
    protected function afterSave($id, array $data, Request $request) {
        // do nothing
    }

    /**
     * The following hooks can be used for additional access checks
     */

    /**
     * this function is called before saving an entry
     * @param int $id
     * @param array $data
     * @param Request $request
     */
    protected function preSave($id, array &$data, Request $request) {
        // do nothing
    }

    /**
     * this function is called before deleting an entry
     * @param int $id
     * @param Request $request
     */
    protected function preDelete($id, Request $request) {
        // do nothing
    }

    /**
     * this function is called before editing an entry
     * @param int $id
     * @param Request $request
     */
    protected function preEdit($id, Request $request) {
        // do nothing
    }

    /**
     * this function is called before getting an entry on a GET API request
     * @param type $id
     * @param Request $request
     */
    protected function preGetAPI($id, Request $request) {
        $data = null;
        $this->preSave($id, $data, $request);
    }

    /**
     * this function is called before returning the entry on a GET API request
     * here you can modify the entry 
     * @param type $id
     * @param type $entry
     * @return type
     */
    protected function afterGetAPI($id, $entry, Request $request) {
        return $entry;
    }

    /**
     * this function is called after successfully deleting an entry
     * @param int $id
     * @param Request $request
     */
    protected function afterDelete($id, Request $request) {
        // do nothing
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $data['user'] = $this->ci->get('helper')->getUser()->id;

        // get user from attribute
        if ($this->user_from_attribute) {
            $user_id = $request->getAttribute('user');
            $user = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
            $data['user'] = $user;
            // use this user for filtering
            $this->mapper->setUser($user);
        }

        $this->insertOrUpdate($id, $data, $request);

        $redirect_url = $this->ci->get('router')->pathFor($this->index_route, $this->index_params);

        return $response->withRedirect($redirect_url, 301);
    }

    protected function insertOrUpdate($id, $data, Request $request) {

        // Remove CSRF attributes
        if (array_key_exists('csrf_name', $data)) {
            unset($data["csrf_name"]);
        }
        if (array_key_exists('csrf_value', $data)) {
            unset($data["csrf_value"]);
        }

        $activity_type = null;

        /**
         * Custom Hook
         */
        $this->preSave($id, $data, $request);

        $entry = new $this->model($data);

        if ($entry->hasParsingErrors()) {
            $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString($entry->getParsingErrors()[0]));
            $this->ci->get('flash')->addMessage('message_type', 'danger');

            $this->logger->addError("Insert failed " . $this->model, array("message" => $this->ci->get('helper')->getTranslatedString($entry->getParsingErrors()[0])));

            return array(false, $entry);
        }

        if ($id == null) {
            $id = $this->mapper->insert($entry);
            $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_SUCCESS_ADD"));
            $this->ci->get('flash')->addMessage('message_type', 'success');
            $this->logger->addNotice("Insert Entry " . $this->model, array("id" => $id));

            $activity_type = "create";
        } else {
            $elements_changed = $this->mapper->update($entry);
            if ($elements_changed > 0) {
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_SUCCESS_UPDATE"));
                $this->ci->get('flash')->addMessage('message_type', 'success');

                $this->logger->addNotice("Update Entry " . $this->model, array("id" => $id));

                $activity_type = "update";
            } else {
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_NOT_CHANGED"));
                $this->ci->get('flash')->addMessage('message_type', 'info');

                $this->logger->addNotice("No Update of Entry " . $this->model, array("id" => $id));
            }
        }

        /**
         * Save m-n user table 
         */
        if ($this->mapper->hasUserTable()) {
            $this->mapper->deleteUsers($id);

            if (array_key_exists("users", $data) && is_array($data["users"])) {
                $users = filter_var_array($data["users"], FILTER_SANITIZE_NUMBER_INT);
                $this->mapper->addUsers($id, $users);
            }
        }
        /**
         * Custom Hook
         */
        $this->afterSave($id, $data, $request);

        /**
         * Add Activity
         */
        if (!is_null($activity_type) && $this->create_activity) {
            try {
                $savedEntry = $this->mapper->get($id);
                $affectedUsers = $this->getAffectedUsers($savedEntry);

                $this->addActivity($activity_type, $id, $savedEntry, $affectedUsers);
            } catch (\Exception $e) {
                $this->logger->addWarning("Could not create activity entry", array("type" => $activity_type, "id" => $id, "error" => $e->getMessage()));
            }
        }

        return array($id, $data);
    }

    public function saveAPI(Request $request, Response $response) {
        try {
            $return = $this->save($request, $response);
        } catch (\Exception $e) {
            $this->logger->addError("Save API " . $this->model, array("error" => $e->getMessage()));

            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }

        return $response->withJSON(array('status' => 'success'));
    }

    public function delete(Request $request, Response $response) {

        $id = $request->getAttribute('id');

        // get user from attribute
        if ($this->user_from_attribute) {
            $user_id = $request->getAttribute('user');
            $user = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
            // use this user for filtering
            $this->mapper->setUser($user);
        }

        $data = ['is_deleted' => false, 'error' => ''];

        try {

            /**
             * Custom Hook
             */
            $this->preDelete($id, $request);

            /**
             * get affected users
             */
            $savedEntry = $this->mapper->get($id);
            $affectedUsers = $this->getAffectedUsers($savedEntry);

            /**
             * Delete
             */
            $is_deleted = $this->mapper->delete($id);
            $data ['is_deleted'] = $is_deleted;
            if ($is_deleted) {
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_SUCCESS_DELETE"));
                $this->ci->get('flash')->addMessage('message_type', 'success');

                $this->logger->addNotice("Delete successfully " . $this->model, array("id" => $id));

                if ($this->create_activity) {
                    $this->addActivity("delete", $id, $savedEntry, $affectedUsers);
                }
            } else {
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_ERROR_DELETE"));
                $this->ci->get('flash')->addMessage('message_type', 'danger');

                $this->logger->addError("Delete failed " . $this->model, array("id" => $id));
            }
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
            $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_ERROR_DELETE"));
            $this->ci->get('flash')->addMessage('message_type', 'danger');

            $this->logger->addError("Delete failed " . $this->model, array("id" => $id, "error" => $e->getMessage()));
        }

        $this->afterDelete($id, $request);

        $newResponse = $response->withJson($data);

        return $newResponse;
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);

            if ($this->mapper->hasUserTable()) {
                $entry_users = $this->mapper->getUsers($entry_id);
                $entry->setUsers($entry_users);
            }
        }

        $users = ($this->mapper->hasUserTable()) ? $this->user_mapper->getAll('name') : array();

        $this->preEdit($entry_id, $request);

        return $this->ci->view->render($response, $this->edit_template, ['entry' => $entry, 'users' => $users]);
    }

    public function getAPI(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        try {
            $this->preGetAPI($entry_id, $request);
            $entry = $this->mapper->get($entry_id);

            if ($this->mapper->hasUserTable()) {
                $entry_users = $this->mapper->getUsers($entry_id);
                $entry->setUsers($entry_users);
            }
            $rentry = $this->afterGetAPI($entry_id, $entry, $request);
        } catch (\Exception $e) {
            $this->logger->addError("Get API " . $this->model, array("id" => $entry_id, "error" => $e->getMessage()));

            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }

        return $response->withJson(['entry' => $rentry]);
    }

    protected function allowOwnerOnly($element_id) {
        $user = $this->ci->get('helper')->getUser()->id;
        if (!is_null($element_id)) {
            $element = $this->mapper->get($element_id);

            if ($element->user !== $user) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

    /**
     * Users with access to a specific dataset
     */
    protected function getAffectedUsers($entry) {
        if ($this->hasParent()) {
            return $this->getParentObjectMapper()->getUsers($entry->getParentID());
        }
        return $this->mapper->getUsers($entry->id);
    }

    protected function getElementViewRoute($entry) {
        if (empty($this->element_view_route)) {
            return null;
        }
        $this->element_view_route_params["id"] = $entry->id;
        return $this->ci->get('router')->pathFor($this->element_view_route, $this->element_view_route_params);
    }

    private function addActivity($type, $id, $entry, $users) {
        $object = ["object" => $this->model, "id" => $id, "description" => $entry->getDescription($this->ci), "link" => $this->getElementViewRoute($entry)];
        $parent = ["object" => $this->parent_model, "id" => $entry->getParentID(), "description" => $this->getParentDescription($entry)];

        $this->ci->get('activity')->addEntry($type, $this->module, static::class, $object, $parent, $users);
    }

    protected function getParentObjectMapper() {
        return null;
    }

    private function getParentDescription($entry) {
        if ($this->hasParent()) {
            $parent_object = $this->getParentObjectMapper()->get($entry->getParentID());
            return $parent_object->getDescription($this->ci);
        }
        return null;
    }

    public function hasParent() {
        return !is_null($this->parent_model) && !is_null($this->getParentObjectMapper());
    }

}
