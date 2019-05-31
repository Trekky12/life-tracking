<?php

namespace App\Splitbill\Group;

class Mapper extends \App\Base\Mapper {

    protected $table = "splitbill_groups";
    protected $model = "\App\Splitbill\Group\Group";
    protected $filterByUser = false;
    protected $insertUser = true;
    protected $hasUserTable = true;
    protected $user_table = "splitbill_groups_user";
    protected $element_name = "sbgroup";
    
}
