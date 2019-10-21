<?php

namespace App\User\MobileFavorites;

class Mapper extends \App\Base\Mapper {

    protected $table = 'global_users_mobile_favorites';
    protected $model = '\App\User\MobileFavorites\MobileFavorite';
    protected $filterByUser = true;
    protected $insertUser = true;
}
