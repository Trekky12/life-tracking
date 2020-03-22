<?php

namespace App\Domain\Finances\Assignment;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Finances\FinancesEntry;

class AssignmentService extends \App\Domain\Service {

    protected $dataobject = \App\Domain\Finances\Assignment\Assignment::class;
    protected $element_view_route = 'finances_categories_assignment_edit';
    protected $module = "finances";

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

    public function getAllAssignmentsOrderedByDescription() {
        return $this->mapper->getAll('description');
    }

    public function findMatchingCategory($user_id, FinancesEntry $entry) {
        return $this->mapper->findMatchingCategory($user_id, $entry->description, $entry->value);
    }

}
