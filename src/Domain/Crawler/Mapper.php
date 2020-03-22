<?php

namespace App\Domain\Crawler;

class Mapper extends \App\Domain\Mapper {

    protected $table = "crawlers";
    protected $dataobject = \App\Domain\Crawler\Crawler::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "crawlers_user";
    protected $element_name = "crawler";

}
