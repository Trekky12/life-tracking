<?php

namespace App\Crawler;

class Mapper extends \App\Base\Mapper {

    protected $table = "crawlers";
    protected $model = "\App\Crawler\Crawler";
    protected $filterByUser = false;
    protected $insertUser = true;
    protected $hasUserTable = true;
    protected $user_table = "crawlers_user";
    protected $element_name = "crawler";

}
