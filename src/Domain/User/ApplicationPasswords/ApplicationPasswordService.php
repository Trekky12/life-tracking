<?php

namespace App\Domain\User\ApplicationPasswords;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class ApplicationPasswordService extends Service {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ApplicationPasswordMapper $mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
    }

    public function getApplicationPasswords() {
        return $this->mapper->getAll('name');
    }

    public function index() {
        $application_passwords = $this->mapper->getAll('name');

        return new Payload(Payload::$RESULT_HTML, ['list' => $application_passwords]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);        
        $password = ApplicationPassword::createPassword();
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'password' => $password]);
    }
}
