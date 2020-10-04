<?php

namespace App\Domain\Workouts\Bodypart;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class BodypartService extends Service {

    public function __construct(LoggerInterface $logger, CurrentUser $user, BodypartMapper $mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
    }

    public function index() {
        $bodyparts = $this->mapper->getAll();
        return new Payload(Payload::$RESULT_HTML, ['bodyparts' => $bodyparts]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry]);
    }

}
