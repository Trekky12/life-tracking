<?php

namespace App\Board;

class CommentMapper extends \App\Base\Mapper {

    protected $table = "comments";
    protected $model = "\App\Board\Comment";
    protected $filterByUser = false; // we don't want to filter the query 
    protected $insertUser = true;   // but insert the user in the database

}
