<?php

namespace App\Domain;

class DataObject implements \JsonSerializable {

    static $NAME = "";
    protected $fields = array();

    /**
     * Save potential parsing errors
     */
    protected $parsing_errors = array();
    protected $users = [];
    protected $additionalData = [];

    protected function parseData(array $data) {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }

    public function __construct(array $data, $additionalData = null) {
        $this->parseData($data);

        $this->additionalData = $additionalData;

        // Always save these values
        $this->user = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->changedOn = $this->exists('changedOn', $data) ? $data['changedOn'] : date('Y-m-d H:i:s');
        
        /**
         * Add users (for m:n)
         */
        if ($this->exists("users", $data) && is_array($data["users"])) {
            $users = filter_var_array($data["users"], FILTER_SANITIZE_NUMBER_INT);
            $this->setUserIDs($users);
        }

        /**
         * Values from DB
         */
        if ($this->exists('id', $data)) {
            $this->id = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT);
        }
        if ($this->exists('createdBy', $data)) {
            $this->createdBy = filter_var($data['createdBy'], FILTER_SANITIZE_NUMBER_INT);
        }
        if ($this->exists('createdOn', $data)) {
            $this->createdOn = filter_var($data['createdOn'], FILTER_SANITIZE_STRING);
        }
        if ($this->exists('changedBy', $data)) {
            $this->changedBy = filter_var($data['changedBy'], FILTER_SANITIZE_NUMBER_INT);
        }
        if ($this->exists('hash', $data)) {
            $this->hash = filter_var($data['hash'], FILTER_SANITIZE_STRING);
        }
    }

    public function __get($key) {
        if (array_key_exists($key, $this->fields)) {
            return $this->fields[$key];
        }
    }

    public function __set($key, $value) {
        $this->fields[$key] = $value;
    }

    /**
     * @see https://twig.sensiolabs.org/doc/2.x/recipes.html#using-dynamic-object-properties
     */
    public function __isset($key) {
        return array_key_exists($key, $this->fields);
    }

    public function get_fields($remove_user_element = false, $insert = true, $update = false) {
        $temp = array();
        foreach ($this->fields as $k => $v) {
            $temp[$k] = $v;
        }

        /**
         * No User
         */
        if ($remove_user_element) {
            if (array_key_exists("user", $temp)) {
                unset($temp["user"]);
            }
        }

        /**
         * Do not output m:n users 
         */
        if ($insert || $update) {
            unset($temp["users"]);
            unset($temp["hash"]);
        }

        return $temp;
    }

    protected function exists($key, $data, $tasker_variables = []) {

        // tasker variables are not resolved? then ignore this value
        if (array_key_exists($key, $data) && in_array($data[$key], $tasker_variables)) {
            return false;
        }

        return array_key_exists($key, $data) && !is_null($data[$key]) && $data[$key] !== "";
    }

    public function hasParsingErrors() {
        return count($this->parsing_errors) > 0;
    }

    public function getParsingErrors() {
        return $this->parsing_errors;
    }

    public function setUserIDs($user_ids) {
        $this->users = array_fill_keys($user_ids, null);
    }

    public function getUserIDs() {
        return array_keys($this->users);
    }

    public function setUsers($users) {
        $this->users = $users;
    }

    public function getUsers() {
        return $this->users;
    }

    public function jsonSerialize() {
        $fields = $this->get_fields(true, false, false);
        if (!empty($this->users)) {
            $fields["users"] = $this->getUserIDs();
        }
        return $fields;
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return "";
    }

    public function getParentID() {
        return null;
    }

    public function getHash() {
        return $this->hash;
    }

    public function addParsingError($error) {
        return $this->parsing_errors[] = $error;
    }

    public function getOwner() {
        if (isset($this->user) && !is_null($this->user)) {
            return $this->user;
        }
        if (isset($this->createdBy) && !is_null($this->createdBy)) {
            return $this->createdBy;
        }
        return null;
    }

    public function copy(){
        $temp = array();
        foreach ($this->fields as $k => $v) {
            $temp[$k] = $v;
        }
        unset($temp["id"]);
        return $temp;
    }

}
