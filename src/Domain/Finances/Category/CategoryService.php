<?php

namespace App\Domain\Finances\Category;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;

class CategoryService extends \App\Domain\Service {

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            CategoryMapper $mapper) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
    }

    public function getAllCategoriesOrderedByName() {
        return $this->mapper->getAll('name');
    }

    public function getCategoryName($id) {
        return $this->mapper->get($id)->name;
    }

    public function getDefaultCategoryOfUser($user_id) {
        return $this->mapper->getDefaultofUser($user_id);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        return ['entry' => $entry];
    }

}
