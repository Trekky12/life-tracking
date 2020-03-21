<?php

namespace App\Notifications\Users;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;

class NotificationUsersService extends \App\Base\Service {

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
