<?php

namespace App\Domain\Recipes\Recipe;

class Recipe extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_RECIPES_RECIPE";

    public function parseData(array $data) {

        // new dataset --> save createdBy 
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }

        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->description = $this->exists('description', $data) ? filter_var($data['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

        $this->preparation_time = $this->exists('preparation_time', $data) ? filter_var($data['preparation_time'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->waiting_time = $this->exists('waiting_time', $data) ? filter_var($data['waiting_time'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->servings = $this->exists('servings', $data) ? filter_var($data['servings'], FILTER_SANITIZE_NUMBER_INT) : 1;

        $this->link = $this->exists('link', $data) ? filter_var($data['link'], FILTER_SANITIZE_STRING) : null;

        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;
        
        // image from database
        if ($this->exists('image', $data)) {
            $this->image = filter_var($data['image'], FILTER_SANITIZE_STRING);
        }
        // update image
        $image = $this->exists('set_image', $data) ? filter_var($data['set_image'], FILTER_SANITIZE_STRING) : null;
        if (!is_null($image)) {
            $this->image = $image;
        }
        // delete image
        if ($this->exists('delete_image', $data)) {
            $this->image = null;
        }

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public function get_thumbnail($size = 'small') {
        if (!empty($this->image)) {
            $file_extension = pathinfo($this->image, PATHINFO_EXTENSION);
            $file_wo_extension = pathinfo($this->image, PATHINFO_FILENAME);
            return $file_wo_extension . '-' . $size . '.' . $file_extension;
        }
        return null;
    }

    public function get_image() {
        if (!empty($this->image)) {
            return $this->image;
        }
        return null;
    }

}
