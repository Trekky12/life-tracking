<?php

namespace App\Finances\Category;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;

class CategoryService extends \App\Base\Service {

    protected $dataobject = \App\Finances\Category\Category::class;
    protected $module = "finances";
    protected $element_view_route = 'finances_categories_edit';

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

    public function getAllCategoriesOrderedByName() {
        return $this->mapper->getAll('name');
    }

    public function getCategoryName($id) {
        return $this->mapper->get($id)->name;
    }

    public function setDefaultCategoryWhenNotSet($id) {
        $cat = $this->mapper->get($id);

        // Set all other non-default, since there can only be one default category
        if ($cat->is_default == 1) {
            $this->mapper->unset_default($id);
        }

        // when there is no default make this the default
        $default = $this->mapper->get_default();
        if (is_null($default)) {
            $this->mapper->set_default($id);
        }
    }

    public function getDefaultCategoryOfUser($user_id) {
        return $this->mapper->getDefaultofUser($user_id);
    }

}
