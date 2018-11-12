<?php

namespace App\User\Notifications;

class Mapper extends \App\Base\Mapper {

    protected $table = 'global_notifications';
    protected $model = '\App\User\Notifications\Notification';
    protected $id = "id";
    protected $filterByUser = false;
    protected $insertUser = true;

    

}
