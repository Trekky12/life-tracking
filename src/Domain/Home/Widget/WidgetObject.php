<?php

namespace App\Domain\Home\Widget;

use App\Domain\Main\Utility\Utility;

class WidgetObject extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_WIDGET_ENTRY";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;
        
        $this->options = $this->exists('options', $data) ? $data['options'] : null;
        
        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;

        $this->is_hidden = $this->exists('is_hidden', $data) ? filter_var($data['is_hidden'], FILTER_SANITIZE_NUMBER_INT) : 0;
    }


    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }
    
    public function getOptions() {
        if(!is_null($this->options)){
            return json_decode($this->options, true);
        }
        return [];
    }

    public function get_fields($remove_user_element = false, $insert = true, $update = false) {
        $temp = parent::get_fields($remove_user_element, $insert, $update);

        if ($insert || $update) {
            $temp["options"] = json_encode($this->options);
        }

        return $temp;
    }

}
