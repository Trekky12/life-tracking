<?php

namespace App\FinancesBudget;

class Budget extends \App\Base\Model {

    public function parseData(array $data) {

        $this->description = $this->exists('description', $data) ? filter_var($data['description'], FILTER_SANITIZE_STRING) : null;
        //$this->category = $this->exists('category', $data) ? filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->value = $this->exists('value', $data) ? filter_var($data['value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->sum = $this->exists('sum', $data) ? filter_var($data['sum'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->percent = $this->exists('percent', $data) ? filter_var($data['percent'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->diff = $this->exists('diff', $data) ? filter_var($data['diff'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        if (is_null($this->description)) {
            $this->parsing_errors[] = "DESCRIPTION_CANNOT_BE_EMPTY";
        }

        /* if (is_null($this->category)) {
          $this->parsing_errors[] = "CATEGORY_CANNOT_BE_EMPTY";
          } */
    }

    /**
     * Remove fields which are not in the db table
     */
    public function get_fields($removeUser = false) {
        $temp = parent::get_fields($removeUser);

        unset($temp["sum"]);
        unset($temp["percent"]);
        unset($temp["diff"]);

        return $temp;
    }

}
