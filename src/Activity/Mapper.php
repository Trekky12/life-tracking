<?php

namespace App\Activity;

class Mapper extends \App\Base\Mapper {

    protected $table = "activities";
    protected $dataobject = \App\Activity\Activity::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "activities_users";
    protected $element_name = "activity";

}
