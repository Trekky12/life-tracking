<?php

namespace App\Domain\Trips;

class Mapper extends \App\Domain\Mapper {

    protected $table = "trips";
    protected $dataobject = \App\Domain\Trips\Trip::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "trips_user";
    protected $element_name = "trip";

}
