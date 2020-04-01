<?php

namespace App\Domain\Finances\Category;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class CategoryWriter extends ObjectActivityWriter {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, CategoryMapper $mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function save($id, $data, $user = null): Payload {
        $payload = parent::save($id, $data, $user);
        $entry = $payload->getResult();

        // set default category
        $this->setDefaultCategoryWhenNotSet($entry->id);

        return $payload;
    }

    private function setDefaultCategoryWhenNotSet($id) {
        $cat = $this->mapper->get($id);

        // Set all other non-default, since there can only be one default category
        if ($cat->is_default == 1) {
            $this->mapper->unset_default($id);
        }

        // when there is no default make this the default
        $default = $this->mapper->get_default();
        if (is_null($default)) {
            $this->mapper->set_default($id);
        }
    }

    public function getObjectViewRoute(): string {
        return 'finances_categories_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "finances";
    }

}
