<?php

namespace App\Timesheets\Project;

class Project extends \App\Base\Model {

    static $MODEL_NAME = "MODEL_TIMESHEETS_PROJECT";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\Interop\Container\ContainerInterface $ci) {
        return $this->name;
    }

}
