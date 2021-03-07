<?php

namespace App\Domain\Timesheets\Project;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Domain\Timesheets\Sheet\SheetMapper;
use App\Application\Payload\Payload;

class ProjectService extends Service {

    private $user_service;
    private $sheet_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ProjectMapper $mapper, UserService $user_service, SheetMapper $sheet_mapper) {
        parent::__construct($logger, $user);

        $this->mapper = $mapper;
        $this->user_service = $user_service;
        $this->sheet_mapper = $sheet_mapper;
    }

    public function index() {
        $projects = $this->mapper->getUserItems('t.createdOn DESC, name');
        
        $times = $this->sheet_mapper->getTimes();

        return new Payload(Payload::$RESULT_HTML, ['projects' => $projects, 'times' => $times]);
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
