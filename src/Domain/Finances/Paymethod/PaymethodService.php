<?php

namespace App\Domain\Finances\Paymethod;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;

class PaymethodService extends \App\Domain\Service {

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            PaymethodMapper $cat_mapper) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $cat_mapper;
    }

    public function getAllPaymethodsOrderedByName() {
        return $this->mapper->getAll('name');
    }

    public function getAllfromUsers($group_users) {
        return $this->mapper->getAllfromUsers($group_users);
    }

    public function index() {
        $paymethods = $this->getAllPaymethodsOrderedByName();
        return ['paymethods' => $paymethods];
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        return ['entry' => $entry];
    }

}
