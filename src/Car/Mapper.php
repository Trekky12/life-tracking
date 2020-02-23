<?php

namespace App\Car;

class Mapper extends \App\Base\Mapper {

    protected $table = "cars";
    protected $model = "\App\Car\Car";
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "cars_user";
    protected $element_name = "car";

}
