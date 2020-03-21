<?php

namespace App\Board;

class Mapper extends \App\Base\Mapper {

    protected $table = "boards";
    protected $dataobject = \App\Board\Board::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "boards_user";
    protected $element_name = "board";


    
}
