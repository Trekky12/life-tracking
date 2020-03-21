<?php

namespace App\Timesheets\Project;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;

class ProjectService extends \App\Base\Service {

    protected $dataobject = \App\Timesheets\Project\Project::class;
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
