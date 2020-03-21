<?php

namespace App\Activity;

class Activity extends \App\Base\DataObject {

    static $NAME = "DATAOBJECT_ACTIVITY";

    public function parseData(array $data) {

        $this->type = $this->exists('type', $data) ? filter_var($data['type'], FILTER_SANITIZE_STRING) : null;
        $this->module = $this->exists('module', $data) ? filter_var($data['module'], FILTER_SANITIZE_STRING) : null;
        $this->controller = $this->exists('controller', $data) ? filter_var($data['controller'], FILTER_SANITIZE_STRING) : null;

        $this->object = $this->exists('object', $data) ? filter_var($data['object'], FILTER_SANITIZE_STRING) : null;
        $this->object_id = $this->exists('object_id', $data) ? intval(filter_var($data['object_id'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $this->object_description = $this->exists('object_description', $data) ? filter_var($data['object_description'], FILTER_SANITIZE_STRING) : null;

        $this->parent_object = $this->exists('parent_object', $data) ? filter_var($data['parent_object'], FILTER_SANITIZE_STRING) : null;
        $this->parent_object_id = $this->exists('parent_object_id', $data) ? intval(filter_var($data['parent_object_id'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $this->parent_object_description = $this->exists('parent_object_description', $data) ? filter_var($data['parent_object_description'], FILTER_SANITIZE_STRING) : null;

        $this->link = $this->exists('link', $data) ? filter_var($data['link'], FILTER_SANITIZE_STRING) : null;
    }

}
