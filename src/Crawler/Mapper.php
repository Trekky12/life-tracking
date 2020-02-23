<?php

namespace App\Crawler;

class Mapper extends \App\Base\Mapper {

    protected $table = "crawlers";
    protected $model = "\App\Crawler\Crawler";
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "crawlers_user";
    protected $element_name = "crawler";

}
