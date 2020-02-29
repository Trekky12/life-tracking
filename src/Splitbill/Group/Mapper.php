<?php

namespace App\Splitbill\Group;

class Mapper extends \App\Base\Mapper {

    protected $table = "splitbill_groups";
    protected $model = "\App\Splitbill\Group\Group";
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "splitbill_groups_user";
    protected $element_name = "sbgroup";

}
