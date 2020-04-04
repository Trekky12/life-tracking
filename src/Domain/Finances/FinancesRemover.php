<?php

namespace App\Domain\Finances;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Finances\FinancesService;

class FinancesRemover extends ObjectActivityRemover {

    private $finances_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, FinancesMapper $mapper, FinancesService $finances_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->finances_service = $finances_service;
    }

    public function delete($id, $additionalData = null): Payload {

        try {
            $is_splitted = $this->finances_service->isSplittedBillEntry($id);
            if ($is_splitted) {
                return new Payload(Payload::$STATUS_ERROR, 'NO_ACCESS');
            } else {
                return parent::delete($id, $additionalData);
            }
        } catch (\Exception $ex) {
            
        }
        return new Payload(Payload::$STATUS_ERROR, 'ELEMENT_NOT_FOUND');
    }

    public function getObjectViewRoute(): string {
        return 'finances_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "finances";
    }

}
