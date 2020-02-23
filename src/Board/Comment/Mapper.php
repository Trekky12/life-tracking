<?php

namespace App\Board\Comment;

class Mapper extends \App\Base\Mapper {

    protected $table = "boards_comments";
    protected $model = "\App\Board\Comment\Comment";
    protected $select_results_of_user_only = false; // we don't want to filter the query 
    protected $insert_user = true;   // but insert the user in the database

}
