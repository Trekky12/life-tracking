<?php

namespace App\Domain\Workouts\Exercise;

class Exercise extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_WORKOUTS_EXERCISE";

    public function parseData(array $data) {

        // new dataset --> save createdBy 
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }

        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

        $this->instructions = $this->exists('instructions', $data) ? filter_var($data['instructions'], FILTER_SANITIZE_STRING) : null;

        $this->category = $this->exists('category', $data) ? filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->level = $this->exists('level', $data) ? filter_var($data['level'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->rating = $this->exists('rating', $data) ? filter_var($data['rating'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->mainBodyPart = $this->exists('mainBodyPart', $data) ? filter_var($data['mainBodyPart'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->mainMuscle = $this->exists('mainMuscle', $data) ? filter_var($data['mainMuscle'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->setImage($data);
        $this->setThumbnail($data);

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    private function setImage($data) {
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
    }

    private function setThumbnail($data) {
        // image from database
        if ($this->exists('thumbnail', $data)) {
            $this->thumbnail = filter_var($data['thumbnail'], FILTER_SANITIZE_STRING);
        }
        // update image
        $image = $this->exists('set_thumbnail', $data) ? filter_var($data['set_thumbnail'], FILTER_SANITIZE_STRING) : null;
        if (!is_null($image)) {
            $this->thumbnail = $image;
        }
        // delete image
        if ($this->exists('delete_thumbnail', $data)) {
            $this->thumbnail = null;
        }
    }

    public function get_thumbnail() {
        if (!empty($this->thumbnail)) {
            return $this->thumbnail;
        }
        return null;
    }

    public function get_image() {
        if (!empty($this->image)) {
            return $this->image;
        }
        return null;
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public static function getCategories() {
        return [
            ["id" => 0, "name" => "WORKOUTS_CATEGORY_REPS"],
            ["id" => 1, "name" => "WORKOUTS_CATEGORY_REPS_WEIGHT"],
            ["id" => 2, "name" => "WORKOUTS_CATEGORY_DISTANCE_TIME"],
            ["id" => 3, "name" => "WORKOUTS_CATEGORY_TIME"]
        ];
    }

}
