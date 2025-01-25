<?php

namespace App\Domain\Workouts\Exercise;

use App\Domain\Main\Utility\Utility;

class Exercise extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_WORKOUTS_EXERCISE";
    public static $WORKOUTS_CATEGORY_REPS = 0;
    public static $WORKOUTS_CATEGORY_REPS_WEIGHT = 1;
    public static $WORKOUTS_CATEGORY_DISTANCE_TIME = 2;
    public static $WORKOUTS_CATEGORY_TIME = 3;

    public function parseData(array $data) {

        // new dataset --> save createdBy 
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }

        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;

        $this->instructions = $this->exists('instructions', $data) ? Utility::filter_string_polyfill($data['instructions']) : null;

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
            $this->image = Utility::filter_string_polyfill($data['image']);
        }
        // update image
        $image = $this->exists('set_image', $data) ? Utility::filter_string_polyfill($data['set_image']) : null;
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
            $this->thumbnail = Utility::filter_string_polyfill($data['thumbnail']);
        }
        // update image
        $image = $this->exists('set_thumbnail', $data) ? Utility::filter_string_polyfill($data['set_thumbnail']) : null;
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
            ["id" => self::$WORKOUTS_CATEGORY_REPS, "name" => "WORKOUTS_CATEGORY_REPS"],
            ["id" => self::$WORKOUTS_CATEGORY_REPS_WEIGHT, "name" => "WORKOUTS_CATEGORY_REPS_WEIGHT"],
            ["id" => self::$WORKOUTS_CATEGORY_DISTANCE_TIME, "name" => "WORKOUTS_CATEGORY_DISTANCE_TIME"],
            ["id" => self::$WORKOUTS_CATEGORY_TIME, "name" => "WORKOUTS_CATEGORY_TIME"]
        ];
    }
    
    public function isCategoryReps(){
        return $this->category == self::$WORKOUTS_CATEGORY_REPS;
    }
    public function isCategoryRepsWeight(){
        return $this->category == self::$WORKOUTS_CATEGORY_REPS_WEIGHT;
    }
    public function isCategoryDistanceTime(){
        return $this->category == self::$WORKOUTS_CATEGORY_DISTANCE_TIME;
    }
    public function isCategoryTime(){
        return $this->category == self::$WORKOUTS_CATEGORY_TIME;
    }
    
    public function getInstructions(){
        return Utility::replaceLinks(nl2br($this->instructions?? ''));
    }
}
