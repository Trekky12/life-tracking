<?php

namespace App\Board;

class Mapper extends \App\Base\Mapper {

    protected $table = "boards";
    protected $model = "\App\Board\Board";
    protected $filterByUser = false;


}
