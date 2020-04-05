<?php

namespace App\Domain\Splitbill\Group;

class GroupMapper extends \App\Domain\Mapper {

    protected $table = "splitbill_groups";
    protected $dataobject = \App\Domain\Splitbill\Group\Group::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "splitbill_groups_user";
    protected $element_name = "sbgroup";

}
