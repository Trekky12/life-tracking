<?php

namespace App\Domain\Timesheets\Project;

class Mapper extends \App\Domain\Mapper {

    protected $table = "timesheets_projects";
    protected $dataobject = \App\Domain\Timesheets\Project\Project::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "timesheets_projects_users";
    protected $element_name = "project";

}
