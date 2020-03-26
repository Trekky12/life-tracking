<?php

namespace App\Domain;

use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use Hashids\Hashids;

abstract class GeneralService {

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

    /* public function isOwner($id) {
      $user = $this->current_user->getUser()->id;
      if (!is_null($id)) {
      $element = $this->mapper->get($id);

      if ($element->user !== $user) {
      return false;
      }
      return true;
      }
      return null;
      }


      public function getUsers($id) {
      return $this->mapper->getUsers($id);
      }

      public function isMember($id) {
      $group_users = $this->getUsers($id);
      $user = $this->current_user->getUser()->id;
      if (!in_array($user, $group_users)) {
      return false;
      }
      return true;
      } */

    public function getFromHash($hash) {
        return $this->mapper->getFromHash($hash);
    }

    public function setHash($id) {
        $entry = $this->mapper->get($id);
        if (empty($entry->getHash())) {
            $hashids = new Hashids('', 10);
            $hash = $hashids->encode($id);
            $this->mapper->setHash($id, $hash);
        }
    }

}
