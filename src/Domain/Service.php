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
        $user = $this->current_user->getUser()->id;
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

    public function isMember($id) {
        $group_users = $this->getUsers($id);
        $user = $this->current_user->getUser()->id;
        if (!array_key_exists($user, $group_users)) {
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

    public function getAll() {
        return $this->mapper->getAll();
    }

}
