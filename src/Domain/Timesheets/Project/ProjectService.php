<?php

namespace App\Domain\Timesheets\Project;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Application\Payload\Payload;

class ProjectService extends Service {

    private $user_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ProjectMapper $mapper, UserService $user_service) {
        parent::__construct($logger, $user);

        $this->mapper = $mapper;
        $this->user_service = $user_service;
    }

    public function index() {
        $projects = $this->mapper->getUserItems('t.createdOn DESC, name');

        return new Payload(Payload::$RESULT_HTML, ['projects' => $projects]);
    }

    public function edit($entry_id) {
        if ($this->isOwner($entry_id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        $users = $this->user_service->getAll();

        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'users' => $users]);
    }
    
    public function getUserProjects() {
        $user = $this->current_user->getUser()->id;
        return $this->mapper->getElementsOfUser($user);
    }

    public function getProjects() {
        return $this->mapper->getAll();
    }

}
