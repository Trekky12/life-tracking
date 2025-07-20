<?php

namespace App\Domain\Workouts\Plan;

use App\Domain\Main\Utility\Utility;

class Plan extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_WORKOUTS_PLAN";
    public static $WORKOUTS_PLAN_CATEGORY_STRENGTH = 0;
    public static $WORKOUTS_PLAN_CATEGORY_MUSCLE = 1;
    public static $WORKOUTS_PLAN_CATEGORY_FAT = 2;
    public static $WORKOUTS_LEVEL_BEGINNER = 0;
    public static $WORKOUTS_LEVEL_INTERMEDIATE = 1;
    public static $WORKOUTS_LEVEL_ADVANCED = 2;

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;
        $this->is_template = $this->exists('is_template', $data) ? intval(filter_var($data['is_template'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $this->notice = $this->exists('notice', $data) ? Utility::filter_string_polyfill($data['notice']) : null;
        $this->category = $this->exists('category', $data) ? filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->level = $this->exists('level', $data) ? filter_var($data['level'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->archive = $this->exists('archive', $data) ? filter_var($data['archive'], FILTER_SANITIZE_NUMBER_INT) : 0;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }

        $this->days = $this->exists('days', $data) ? Utility::filter_string_polyfill($data['days']) : null;
        $this->exercises = $this->exists('exercises', $data) ? filter_var($data['exercises'], FILTER_UNSAFE_RAW) : null;
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public static function getCategories() {
        return [
            ["id" => self::$WORKOUTS_PLAN_CATEGORY_STRENGTH, "name" => "WORKOUTS_PLAN_CATEGORY_STRENGTH"],
            ["id" => self::$WORKOUTS_PLAN_CATEGORY_MUSCLE, "name" => "WORKOUTS_PLAN_CATEGORY_MUSCLE"],
            ["id" => self::$WORKOUTS_PLAN_CATEGORY_FAT, "name" => "WORKOUTS_PLAN_CATEGORY_FAT"]
        ];
    }

    public static function getLevels() {
        return [
            ["id" => self::$WORKOUTS_LEVEL_BEGINNER, "name" => "WORKOUTS_LEVEL_BEGINNER"],
            ["id" => self::$WORKOUTS_LEVEL_INTERMEDIATE, "name" => "WORKOUTS_LEVEL_INTERMEDIATE"],
            ["id" => self::$WORKOUTS_LEVEL_ADVANCED, "name" => "WORKOUTS_LEVEL_ADVANCED"]
        ];
    }

    public function get_fields($remove_user_element = false, $insert = true, $update = false) {
        $temp = parent::get_fields($remove_user_element, $insert, $update);

        unset($temp["days"]);
        unset($temp["exercises"]);

        return $temp;
    }

}
