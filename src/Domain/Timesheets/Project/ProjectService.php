<?php

namespace App\Domain\Timesheets\Project;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;

class ProjectService extends \App\Domain\Service {

    protected $dataobject = \App\Domain\Timesheets\Project\Project::class;
    protected $element_view_route = 'timesheets_projects_edit';
    protected $module = "timesheets";

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

    public function getUserProjects() {
        return $this->mapper->getUserItems('t.createdOn DESC, name');
    }

}
