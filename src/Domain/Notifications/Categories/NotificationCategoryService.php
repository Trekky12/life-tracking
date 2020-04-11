<?php

namespace App\Domain\Notifications\Categories;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class NotificationCategoryService extends Service {

    public function __construct(LoggerInterface $logger, CurrentUser $user, NotificationCategoryMapper $mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
    }

    public function getAllCategories() {
        return $this->mapper->getAll('name');
    }

    private function getCustomCategories() {
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

    public function index() {
        $categories = $this->getCustomCategories();
        return new Payload(Payload::$RESULT_HTML, ['categories' => $categories]);
    }

    public function edit($entry_id) {
        if (!is_null($entry_id) && $this->isInternalCategory($entry_id)) {
            return new Payload(Payload::$NO_ACCESS);
        }

        $entry = $this->getEntry($entry_id);
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry]);
    }

}
