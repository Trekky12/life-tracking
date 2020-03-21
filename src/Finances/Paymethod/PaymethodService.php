<?php

namespace App\Finances\Paymethod;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;

class PaymethodService extends \App\Base\Service {

    protected $dataobject = \App\Finances\Paymethod\Paymethod::class;
    protected $element_view_route = 'finances_paymethod_edit';
    protected $module = "finances";

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $cat_mapper) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $cat_mapper;
    }

    public function getAllPaymethodsOrderedByName() {
        return $this->mapper->getAll('name');
    }

    public function setDefaultPaymethodWhenNotSet($id) {

        $method = $this->mapper->get($id);

        // Set all other non-default, since there can only be one default category
        if ($method->is_default == 1) {
            $this->mapper->unset_default($id);
        }

        // when there is no default make this the default
        $default = $this->mapper->get_default();
        if (is_null($default)) {
            $this->mapper->set_default($id);
        }
    }

    public function getAllfromUsers($group_users) {
        return $this->mapper->getAllfromUsers($group_users);
    }

}
