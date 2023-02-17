<?php

namespace App\Domain\Recipes\Grocery;

class Grocery extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_RECIPES_GROCERY";

    public function parseData(array $data) {
        // new dataset --> save createdBy 
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }

        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

        $this->unit = $this->exists('unit', $data) ? filter_var($data['unit'], FILTER_SANITIZE_STRING) : null;

        $this->is_food = $this->exists('is_food', $data) ? filter_var($data['is_food'], FILTER_SANITIZE_NUMBER_INT) : 0;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

}
