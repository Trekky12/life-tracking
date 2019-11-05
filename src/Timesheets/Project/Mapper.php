<?php

namespace App\Timesheets\Project;

class Mapper extends \App\Base\Mapper {

    protected $table = "timesheets_projects";
    protected $model = "\App\Timesheets\Project\Project";
    protected $filterByUser = false;
    protected $insertUser = true;
    protected $hasUserTable = true;
    protected $user_table = "timesheets_projects_users";
    protected $element_name = "project";
    
}
