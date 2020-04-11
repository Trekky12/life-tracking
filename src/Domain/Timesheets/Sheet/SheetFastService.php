<?php

namespace App\Domain\Timesheets\Sheet;

use Psr\Log\LoggerInterface;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Domain\Timesheets\Project\ProjectService;
use App\Application\Payload\Payload;

class SheetFastService extends SheetService {

    private $translation;

    public function __construct(LoggerInterface $logger, CurrentUser $user, SheetMapper $mapper, ProjectService $project_service, UserService $user_service, Settings $settings, RouteParser $router) {
        parent::__construct($logger, $user, $mapper, $project_service, $user_service, $settings, $router);
    }

    

    

}
