<?php

namespace App\Trips;

class Mapper extends \App\Base\Mapper {

    protected $table = "trips";
    protected $dataobject = \App\Trips\Trip::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "trips_user";
    protected $element_name = "trip";

}
