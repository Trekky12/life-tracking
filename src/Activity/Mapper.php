<?php

namespace App\Activity;

class Mapper extends \App\Base\Mapper {

    protected $table = "activities";
    protected $model = "\App\Activity\Activity";
    protected $filterByUser = false;
    protected $insertUser = true;


    protected $hasUserTable = true;
    protected $user_table = "activities_users";
    protected $element_name = "activity";
}
