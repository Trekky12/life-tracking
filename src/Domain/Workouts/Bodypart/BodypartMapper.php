<?php

namespace App\Domain\Workouts\Bodypart;

class BodypartMapper extends \App\Domain\Mapper {

    protected $table = "workouts_bodyparts";
    protected $dataobject = \App\Domain\Workouts\Bodypart\Bodypart::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    
}
