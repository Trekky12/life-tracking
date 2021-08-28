<?php

namespace App\Application\Error;

class CSRFException extends \Exception {

    private $data;

    public function setData(array $data = []) {
        $this->data = $data;
    }
    
    public function getData(){
        return $this->data;
    }

}
