<?php

namespace App\Domain\Home\Widget;

class WidgetObject extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_WIDGET_ENTRY";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? trim(filter_var($data['name'], FILTER_SANITIZE_STRING)) : null;
        
        $this->options = $this->exists('options', $data) ? $data['options'] : null;
        
        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;
    }


    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }
    
    public function getOptions() {
        return json_decode($this->options, true);
    }

    public function get_fields($remove_user_element = false, $insert = true, $update = false) {
        $temp = parent::get_fields($remove_user_element, $insert, $update);

        if ($insert || $update) {
            $temp["options"] = json_encode($this->options);
        }

        return $temp;
    }

}
