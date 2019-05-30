<?php

namespace App\Trips;

class Mapper extends \App\Base\Mapper {

    protected $table = "trips";
    protected $model = "\App\Trips\Trip";
    protected $filterByUser = false;
    protected $insertUser = true;
    protected $hasUserTable = true;
    protected $user_table = "trips_user";
    protected $element_name = "trip";

    

}