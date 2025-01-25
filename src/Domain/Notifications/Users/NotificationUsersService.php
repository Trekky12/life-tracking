<?php

namespace App\Domain\Notifications\Users;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Main\Utility\Utility;

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

        $cat = array_key_exists('category', $data) ? Utility::filter_string_polyfill($data['category']) : "";
        $type = array_key_exists('type', $data) ? intval(filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $category = intval($cat);
        $object_id = null;
        if(strpos($cat, "_")){
            $cat_and_id = explode("_", $cat);
            $category = intval($cat_and_id[0]);
            $object_id = intval($cat_and_id[1]);
        }       
        
        $user = $this->current_user->getUser();

        if ($type == 1) {
            $this->mapper->addCategory($user->id, $category, $object_id);
        } else {
            $this->mapper->deleteCategory($user->id, $category, $object_id);
        }
        $result = ["status" => "success"];
        return new Payload(Payload::$RESULT_JSON, $result);
    }
    
    public function doesUserHaveCategory($category, $user, $object_id) {
        return $this->mapper->doesUserHaveCategory($category, $user, $object_id);
    }

}
