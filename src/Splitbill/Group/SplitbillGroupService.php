<?php

namespace App\Splitbill\Group;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;

class SplitbillGroupService extends \App\Base\Service {

    protected $dataobject = \App\Splitbill\Group\Group::class;
    protected $element_view_route = 'splitbill_groups_edit';
    protected $module = "splitbills";

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

    public function getUserGroups() {
        return $this->mapper->getUserItems('t.createdOn DESC, name');
    }

}
