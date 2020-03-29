<?php

namespace App\Domain\Car;

class CarMapper extends \App\Domain\Mapper {

    protected $table = "cars";
    protected $dataobject = \App\Domain\Car\Car::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "cars_user";
    protected $element_name = "car";

}
