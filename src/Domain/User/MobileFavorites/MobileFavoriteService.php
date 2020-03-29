<?php

namespace App\Domain\User\MobileFavorites;

use App\Domain\GeneralService;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class MobileFavoriteService extends GeneralService {

    public function __construct(LoggerInterface $logger, CurrentUser $user, MobileFavoritesMapper $mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
    }

    public function getMobileFavorites() {
        return $this->mapper->getAll('position');
    }

    public function index() {
        $favorites = $this->mapper->getAll('position');

        return new Payload(Payload::$RESULT_HTML, ['list' => $favorites]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry]);
    }

}
