<?php

namespace App\Domain\Finances\Assignment;

use App\Domain\GeneralService;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Finances\FinancesEntry;
use App\Domain\Finances\Category\CategoryService;

class AssignmentService extends GeneralService {

    private $cat_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, AssignmentMapper $mapper, CategoryService $cat_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->cat_service = $cat_service;
    }

    public function getAllAssignmentsOrderedByDescription() {
        return $this->mapper->getAll('description');
    }

    public function findMatchingCategory($user_id, FinancesEntry $entry) {
        return $this->mapper->findMatchingCategory($user_id, $entry->description, $entry->value);
    }

    public function index() {
        $assignments = $this->getAllAssignmentsOrderedByDescription();
        $categories = $this->cat_service->getAllCategoriesOrderedByName();
        return ['assignments' => $assignments, 'categories' => $categories];
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        $categories = $this->cat_service->getAllCategoriesOrderedByName();
        return ['entry' => $entry, 'categories' => $categories];
    }

}
