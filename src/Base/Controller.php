<?php

namespace App\Base;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

abstract class Controller {

    protected $ci;
    protected $mapper;
    protected $model;
    protected $index_route;
    protected $edit_template;
    protected $user_mapper;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
        $this->init();

        $this->user_mapper = new \App\User\Mapper($this->ci);
    }

    /**
     * Initialize the main variables
     * @var $model
     * @var $index
     * @var $edit_template;
     */
    abstract function init();

    /**
     * this function is called after successfully saving an entry
     * @param type $id
     * @param type $data
     */
    protected function afterSave($id, $data) {
        // do nothing
    }

    /**
     * The following hooks can be used for additional access checks
     */

    /**
     * this function is called before saving an entry
     * @param type $id
     * @param type $data
     */
    protected function preSave($id, $data) {
        // do nothing
    }

    /**
     * this function is called before deleting an entry
     * @param type $id
     * @param type $data
     */
    protected function preDelete($id) {
        // do nothing
    }

    /**
     * this function is called before editing an entry
     * @param type $id
     * @param type $data
     */
    protected function preEdit($id) {
        // do nothing
    }

    /**
     * this function is called before getting an entry on a GET API request
     * @param type $id
     * @param type $entry
     */
    protected function preGetAPI($id) {
        $this->preSave($id, null);
    }

    /**
     * this function is called before returning the entry on a GET API request
     * here you can modify the entry 
     * @param type $id
     * @param type $entry
     * @return type
     */
    protected function afterGetAPI($id, $entry) {
        return $entry;
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $data['user'] = $this->ci->get('helper')->getUser()->id;

        $this->insertOrUpdate($id, $data);

        return $response->withRedirect($this->ci->get('router')->pathFor($this->index_route), 301);
    }

    protected function insertOrUpdate($id, $data) {
        $entry = new $this->model($data);

        if ($entry->hasParsingErrors()) {
            $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString($entry->getParsingErrors()[0]));
            $this->ci->get('flash')->addMessage('message_type', 'danger');
        } else {

            /**
             * Custom Hook
             */
            $this->preSave($id, $data);

            if ($id == null) {
                $id = $this->mapper->insert($entry);
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_SUCCESS_ADD"));
                $this->ci->get('flash')->addMessage('message_type', 'success');
            } else {
                $elements_changed = $this->mapper->update($entry);
                if ($elements_changed > 0) {
                    $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_SUCCESS_UPDATE"));
                    $this->ci->get('flash')->addMessage('message_type', 'success');
                } else {
                    $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_NOT_CHANGED"));
                    $this->ci->get('flash')->addMessage('message_type', 'info');
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
            $this->afterSave($id, $data);
        }
        return array($id, $entry);
    }

    public function saveAPI(Request $request, Response $response) {
        try {
            $return = $this->save($request, $response);
        } catch (\Exception $e) {
            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }

        return $response->withJSON(array('status' => 'success'));
    }

    public function delete(Request $request, Response $response) {

        $id = $request->getAttribute('id');

        /**
         * Custom Hook
         */
        $this->preDelete($id);

        $data = ['is_deleted' => false, 'error' => ''];

        try {
            $is_deleted = $this->mapper->delete($id);
            $data ['is_deleted'] = $is_deleted;
            if ($is_deleted) {
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_SUCCESS_DELETE"));
                $this->ci->get('flash')->addMessage('message_type', 'success');
            } else {
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_ERROR_DELETE"));
                $this->ci->get('flash')->addMessage('message_type', 'danger');
            }
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
            $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_ERROR_DELETE"));
            $this->ci->get('flash')->addMessage('message_type', 'danger');
        }

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

        $this->preEdit($entry_id);

        return $this->ci->view->render($response, $this->edit_template, ['entry' => $entry, 'users' => $users]);
    }

    public function getAPI(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        try {
            $this->preGetAPI($entry_id);
            $entry = $this->mapper->get($entry_id);

            if ($this->mapper->hasUserTable()) {
                $entry_users = $this->mapper->getUsers($entry_id);
                $entry->setUsers($entry_users);
            }
            $rentry = $this->afterGetAPI($entry_id, $entry);
        } catch (\Exception $e) {
            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }

        return $response->withJson(['entry' => $rentry]);
    }

}
