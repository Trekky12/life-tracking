<?php

namespace App\Domain;

use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;

abstract class Service {

    protected $logger;
    protected $current_user;
    protected $mapper = null;

    public function __construct(LoggerInterface $logger, CurrentUser $user) {
        $this->logger = $logger;
        $this->current_user = $user;
    }

    public function getEntry($entry_id, $user = null) {
        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);

            if ($this->mapper->hasUserTable()) {
                $entry_users = $this->mapper->getUsers($entry_id);
                $entry->setUsers($entry_users);
            }
        }

        return $entry;
    }

    function getMapper() {
        return $this->mapper;
    }

    public function isOwner($id) {
        $user = $this->current_user->getUser()?->id;
        if (!is_null($id)) {
            $element = $this->getMapper()->get($id);

            if ($element->getOwner() !== $user) {
                return false;
            }
            return true;
        }
        return null;
    }

    public function getUsers($id) {
        return $this->getMapper()->getUsers($id);
    }

    public function isMember($group_id) {
        $group_users = $this->getUsers($group_id);
        $user = $this->current_user->getUser()->id;
        if (array_key_exists($user, $group_users)) {
            return true;
        }
        return false;
    }

    public function isChildOf($group_id, $entry_id = null) {
        // check if parent is not matching to the entries parent
        if(!is_null($entry_id)){
            $entry = $this->getEntry($entry_id);
            $parent_id = $entry->getParentID();
            if($group_id === $parent_id){
                return true;
            }
            return false;
        }
        return true;
    }

    public function getFromHash($hash) {
        return $this->getMapper()->getFromHash($hash);
    }

    public function getUserElements() {
        $user = $this->current_user->getUser()->id;
        return $this->mapper->getElementsOfUser($user);
    }

    public function getParent($id){

    }

    public function getAll() {
        return $this->mapper->getAll();
    }

}
