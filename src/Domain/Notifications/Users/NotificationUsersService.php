<?php

namespace App\Domain\Notifications\Users;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class NotificationUsersService extends Service {

    public function __construct(LoggerInterface $logger, CurrentUser $user, NotificationUsersMapper $mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
    }

    public function getCategoriesByUser($user) {
        return $this->mapper->getCategoriesByUser($user);
    }

    public function getUsersByCategory($category) {
        return $this->mapper->getUsersByCategory($category);
    }

    public function setCategoryForUser($data) {

        $category = array_key_exists('category', $data) ? intval(filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $type = array_key_exists('type', $data) ? intval(filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT)) : 0;


        $user = $this->current_user->getUser();

        if ($type == 1) {
            $this->mapper->addCategory($user->id, $category);
        } else {
            $this->mapper->deleteCategory($user->id, $category);
        }
        $result = ["status" => "success"];
        return new Payload(Payload::$RESULT_JSON, $result);
    }

}
