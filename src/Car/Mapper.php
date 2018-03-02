<?php

namespace App\Car;

class Mapper extends \App\Base\Mapper {

    protected $table = "cars";
    protected $model = "\App\Car\Car";
    protected $filterByUser = false;
    protected $hasUserTable = true;
    protected $user_table = "cars_user";
    protected $element_name = "car";

}
