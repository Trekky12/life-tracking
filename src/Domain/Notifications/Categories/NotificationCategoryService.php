<?php

namespace App\Domain\Notifications\Categories;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;

class NotificationCategoryService extends \App\Domain\Service {

    protected $dataobject = \App\Domain\Notifications\Categories\Category::class;
    protected $element_view_route = 'notifications_categories_edit';
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

    public function getAllCategories() {
        return $this->mapper->getAll('name');
    }

    public function getCustomCategories() {
        $categories = $this->mapper->getAll('name');
        $categories_filtered = array_filter($categories, function($cat) {
            return !$cat->isInternal();
        });

        return $categories_filtered;
    }

    public function isInternalCategory($id) {
        $cat = $this->mapper->get($id);
        if ($cat->isInternal()) {
            return true;
        }
        return false;
    }

    public function getCategoryByIdentifier($identifier) {
        return $this->mapper->getCategoryByIdentifier($identifier);
    }

}
