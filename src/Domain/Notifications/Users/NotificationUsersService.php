<?php

namespace App\Domain\Notifications\Users;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;

class NotificationUsersService extends \App\Domain\Service {

    protected $module = "notifications";

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

    public function getCategoriesByUser($user) {
        return $this->mapper->getCategoriesByUser($user);
    }

    public function getUsersByCategory($category) {
        return $this->mapper->getUsersByCategory($category);
    }

    public function setCategoryForUser($category, $type) {
        $user = $this->current_user->getUser();

        if ($type == 1) {
            $this->mapper->addCategory($user->id, $category);
        } else {
            $this->mapper->deleteCategory($user->id, $category);
        }
    }

}
