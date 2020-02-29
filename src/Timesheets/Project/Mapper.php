<?php

namespace App\Timesheets\Project;

class Mapper extends \App\Base\Mapper {

    protected $table = "timesheets_projects";
    protected $model = "\App\Timesheets\Project\Project";
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "timesheets_projects_users";
    protected $element_name = "project";

}
