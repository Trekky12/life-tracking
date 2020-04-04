<?php

namespace App\Domain\Location;

use App\Domain\ObjectWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Main\Utility\Utility;

class LocationWriter extends ObjectWriter {

    public function __construct(LoggerInterface $logger, CurrentUser $user, LocationMapper $mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
    }

    public function save($id, $data, $additionalData = null): Payload {

        if (!array_key_exists("device", $data)) {
            $data["device"] = Utility::getAgent();
        }

        return parent::save($id, $data, $additionalData);
    }

}
