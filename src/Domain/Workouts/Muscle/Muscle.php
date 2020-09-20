<?php

namespace App\Domain\Workouts\Muscle;

class Muscle extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_WORKOUTS_MUSCLE";

    public function parseData(array $data) {

        // new dataset --> save createdBy 
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }

        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

        $this->setImagePrimary($data);
        $this->setImageSecondary($data);

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    private function setImagePrimary($data) {
        // image from database
        if ($this->exists('image_primary', $data)) {
            $this->image_primary = filter_var($data['image_primary'], FILTER_SANITIZE_STRING);
        }
        // update image
        $image = $this->exists('set_image_primary', $data) ? filter_var($data['set_image_primary'], FILTER_SANITIZE_STRING) : null;
        if (!is_null($image)) {
            $this->image_primary = $image;
        }
        // delete image
        if ($this->exists('delete_image_primary', $data)) {
            $this->image_primary = null;
        }
    }

    private function setImageSecondary($data) {
        // image from database
        if ($this->exists('image_secondary', $data)) {
            $this->image_secondary = filter_var($data['image_secondary'], FILTER_SANITIZE_STRING);
        }
        // update image
        $image = $this->exists('set_image_secondary', $data) ? filter_var($data['set_image_secondary'], FILTER_SANITIZE_STRING) : null;
        if (!is_null($image)) {
            $this->image_secondary = $image;
        }
        // delete image
        if ($this->exists('delete_image_secondary', $data)) {
            $this->image_secondary = null;
        }
    }

    public function get_image_primary($thumbnail = false) {
        if (!empty($this->image_primary)) {
            return $thumbnail ? $this->get_thumbnail_name($this->image_primary) : $this->image_primary;
        }
        return null;
    }

    public function get_image_secondary($thumbnail = false) {
        if (!empty($this->image_secondary)) {
            return $thumbnail ? $this->get_thumbnail_name($this->image_secondary) : $this->image_secondary;
        }
        return null;
    }

    private function get_thumbnail_name($image, $size = "small") {
        $file_extension = pathinfo($image, PATHINFO_EXTENSION);
        $file_wo_extension = pathinfo($image, PATHINFO_FILENAME);
        return $file_wo_extension . '-' . $size . '.' . $file_extension;
    }

}
