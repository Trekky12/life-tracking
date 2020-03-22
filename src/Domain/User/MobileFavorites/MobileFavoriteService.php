<?php

namespace App\Domain\User\MobileFavorites;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;

class MobileFavoriteService extends \App\Domain\Service {

    protected $dataobject = \App\Domain\User\MobileFavorites\MobileFavorite::class;
    protected $element_view_route = 'users_mobile_favorites_edit';

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
    }

    public function getMobileFavorites() {
        return $this->mapper->getAll('position');
    }

}
