<?php

namespace App\Domain\Workouts\Plan;

class PlanMapper extends \App\Domain\Mapper {

    protected $table = "workouts_plans";
    protected $dataobject = \App\Domain\Workouts\Plan\Plan::class;
    protected $select_results_of_user_only = true;
    protected $insert_user = true;
    //protected $has_user_table = false;
    //protected $user_table = "timesheets_projects_users";
    //protected $element_name = "project";

}
