<?php

namespace App\Domain\Workouts\Muscle;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class MuscleService extends Service {

    public function __construct(LoggerInterface $logger, CurrentUser $user, MuscleMapper $mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
    }

    public function index() {
        $muscles = $this->mapper->getAll();
        return new Payload(Payload::$RESULT_HTML, ['muscles' => $muscles]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry]);
    }

}
