<?php

namespace App\Application\Payload;

class Payload {

    private $status;
    private $result;
    private $flash_messages = [];
    public static $STATUS_NEW = "NEW";
    public static $STATUS_UPDATE = "UPDATED";
    public static $STATUS_NO_UPDATE = "NOT_UPDATED";
    public static $STATUS_PARSING_ERRORS = "PARSING_ERRORS";
    public static $STATUS_ERROR = "UNDEFINED_ERROR";
    public static $STATUS_DELETE_SUCCESS = "DELETE_SUCCESS";
    public static $STATUS_DELETE_ERROR = "DELETE_ERROR";
    public static $RESULT_ARRAY = "RESULT_ARRAY";

    public function __construct($status, $result, $flash_messages = []) {
        $this->status = $status;
        $this->result = $result;
        $this->flash_messages = $flash_messages;
    }

    function getStatus() {
        return $this->status;
    }

    function getResult() {
        return $this->result;
    }

    function getFlashMessages() {
        return $this->flash_messages;
    }

    public function addFlashMessage($key, $value) {
        $this->flash_messages[$key] = $value;
    }

    public function __toString() {
        return $this->status;
    }

}
