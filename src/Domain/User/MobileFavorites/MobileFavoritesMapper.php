<?php

namespace App\Domain\User\MobileFavorites;

class MobileFavoritesMapper extends \App\Domain\Mapper {

    protected $table = 'global_users_mobile_favorites';
    protected $dataobject = \App\Domain\User\MobileFavorites\MobileFavorite::class;
    protected $select_results_of_user_only = true;
    protected $insert_user = true;

}
