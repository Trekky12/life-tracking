<?php

namespace App\Board\Comment;

class Mapper extends \App\Base\Mapper {

    protected $table = "boards_comments";
    protected $model = "\App\Board\Comment\Comment";
    protected $filterByUser = false; // we don't want to filter the query 
    protected $insertUser = true;   // but insert the user in the database

}
