<?php

namespace App\Activity;

class Mapper extends \App\Base\Mapper {

    protected $table = "activities";
    protected $model = "\App\Activity\Activity";
    protected $select_results_of_user_only = false;
    protected $insert_user = true;


    protected $has_user_table = true;
    protected $user_table = "activities_users";
    protected $element_name = "activity";
}
