<?php

namespace App\Base;

class Model implements \JsonSerializable {

    protected $fields = array();

    /**
     * Save potential parsing errors
     */
    protected $parsing_errors = array();

    protected function parseData(array $data) {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }

    public function __construct(array $data) {
        $this->parseData($data);

        $this->user = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;

        if ($this->exists('id', $data)) {
            $this->id = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT);
        }

        $this->changedOn = $this->exists('changedOn', $data) ? $data['changedOn'] : date('Y-m-d G:i:s');
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

    public function get_fields($removeUser = false) {
        $temp = array();
        foreach ($this->fields as $k => $v) {
            $temp[$k] = $v;
        }

        /**
         * No User
         */
        if ($removeUser) {
            if (array_key_exists("user", $temp)) {
                unset($temp["user"]);
            }
        }
        return $temp;
    }

    public function hasElements() {
        return count($this->fields) > 0;
    }

    protected function exists($key, $data) {
        return array_key_exists($key, $data) && isset($data[$key]) && $data[$key] != "" && $data[$key] != '%' . $key;
    }

    public function log() {
        $object = get_object_vars($this);
        return array('IP' => $_SERVER["REMOTE_ADDR"], 'object' => $object);
    }

    public function hasParsingErrors() {
        return count($this->parsing_errors) > 0;
    }

    public function getParsingErrors() {
        return $this->parsing_errors;
    }

    public function setUsers($users) {
        $this->users = $users;
    }

    public function getUsers() {
        return $this->users;
    }

    public function jsonSerialize() {
        return $this->get_fields(true);
    }

}
