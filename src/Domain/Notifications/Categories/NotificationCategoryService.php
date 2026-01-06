<?php

namespace App\Domain\Notifications\Categories;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Application\Payload\Payload;

class NotificationCategoryService extends Service {
    
    private $user_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, NotificationCategoryMapper $mapper, UserService $user_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->user_service = $user_service;
    }

    public function getAllCategories() {
        return $this->mapper->getAll('name');
    }

    private function getCustomCategories() {
        $categories = $this->mapper->getAll('name');
        $categories_filtered = array_filter($categories, function($cat) {
            return !$cat->isInternal() && !$cat->hasReminder();
        });

        return $categories_filtered;
    }

    public function isInternalCategory($id) {
        $cat = $this->mapper->get($id);
        if ($cat->isInternal() && !$cat->hasReminder()) {
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
        $users = $this->user_service->getAll();
        
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'users' => $users]);
    }
    
    public function getUserCategories(){
        return $this->mapper->getUserCategories('t.createdOn DESC, name');
    }

    public function getCategoryByReminder($reminder) {
        return $this->mapper->getCategoryByReminder($reminder);
    }

}
