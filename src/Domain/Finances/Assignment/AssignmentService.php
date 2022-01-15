<?php

namespace App\Domain\Finances\Assignment;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Finances\FinancesEntry;
use App\Domain\Finances\Category\CategoryService;
use App\Application\Payload\Payload;

class AssignmentService extends Service {

    private $cat_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, AssignmentMapper $mapper, CategoryService $cat_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->cat_service = $cat_service;
    }

    public function getAllAssignmentsOrderedByDescription() {
        return $this->mapper->getAll('description');
    }

    public function findMatchingCategory(FinancesEntry $entry) {
        return $this->mapper->findMatchingCategory($entry->user, $entry->description, $entry->value);
    }

    public function index() {
        $assignments = $this->getAllAssignmentsOrderedByDescription();
        $categories = $this->cat_service->getAllCategoriesOrderedByName();
        return new Payload(Payload::$RESULT_HTML, ['assignments' => $assignments, 'categories' => $categories]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        $categories = $this->cat_service->getAllCategoriesOrderedByName();
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'categories' => $categories]);
    }

}
