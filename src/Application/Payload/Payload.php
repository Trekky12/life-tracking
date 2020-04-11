<?php

namespace App\Application\Payload;

class Payload {

    private $status;
    private $result;
    private $flash_messages = [];
    private $routeName = null;
    private $routeParams = [];
    private $template = null;
    private $additionalData = [];
    public static $STATUS_NEW = "NEW";
    public static $STATUS_UPDATE = "UPDATED";
    public static $STATUS_NO_UPDATE = "NOT_UPDATED";
    public static $STATUS_PARSING_ERRORS = "PARSING_ERRORS";
    public static $STATUS_ERROR = "UNDEFINED_ERROR";
    public static $STATUS_DELETE_SUCCESS = "DELETE_SUCCESS";
    public static $STATUS_DELETE_ERROR = "DELETE_ERROR";
    public static $RESULT_ARRAY = "RESULT_ARRAY";
    public static $RESULT_JSON = "RESULT_JSON";
    public static $RESULT_HTML = "RESULT_HTML";
    public static $RESULT_RAW = "RESULT_RAW";
    public static $NO_ACCESS = "NO_ACCESS";
    public static $JSON_NO_ACCESS = "JSON_NO_ACCESS";
    public static $STATUS_PASSWORD_MISSMATCH = "PASSWORD_MISSMATCH";
    public static $STATUS_PASSWORD_WRONG = "PASSWORD_WRONG";
    public static $STATUS_PASSWORD_SUCCESS = "PASSWORD_SUCCESS";
    public static $STATUS_PROFILE_IMAGE_DELETED = "PROFILE_IMAGE_DELETED";
    public static $STATUS_PROFILE_IMAGE_ERROR = "PROFILE_IMAGE_ERROR";
    public static $STATUS_PROFILE_IMAGE_SET = "PROFILE_IMAGE_SET";
    public static $STATUS_MAIL_SUCCESS = "MAIL_SUCCESS";
    public static $STATUS_MAIL_ERROR = "MAIL_ERROR";
    public static $STATUS_NO_MAIL = "MAIL_NO_MAIL";
    public static $STATUS_NOTIFICATION_SUCCESS = "NOTIFICATION_SUCCESS";
    public static $STATUS_NOTIFICATION_FAILURE = "NOTIFICATION_FAILURE";

    public function __construct($status, $result = null, $additionalData = [], $flash_messages = []) {
        $this->status = $status;
        $this->result = $result;
        $this->additionalData = $additionalData;
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

    public function clearFlashMessage($key) {
        $this->flash_messages[$key] = null;
        unset($this->flash_messages[$key]);
    }

    public function __toString() {
        return $this->status;
    }

    public function withRouteName($routeName) {
        $clone = clone $this;
        $clone->routeName = $routeName;

        return $clone;
    }

    public function getRouteName() {
        return $this->routeName;
    }

    public function withTemplate($templateName) {
        $clone = clone $this;
        $clone->template = $templateName;

        return $clone;
    }

    public function getTemplate() {
        return $this->template;
    }

    public function withAdditonalData($data) {
        $clone = clone $this;
        $clone->additionalData = $data;

        return $clone;
    }

    public function getAdditonalData() {
        return $this->additionalData;
    }

    public function withRouteParams($data) {
        $clone = clone $this;
        $clone->routeParams = $data;

        return $clone;
    }

    public function getRouteParams() {
        return $this->routeParams;
    }

}
