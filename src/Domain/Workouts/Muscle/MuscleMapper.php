<?php

namespace App\Domain\Workouts\Muscle;

class MuscleMapper extends \App\Domain\Mapper {

    protected $table = "workouts_muscles";
    protected $dataobject = \App\Domain\Workouts\Muscle\Muscle::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    
}
