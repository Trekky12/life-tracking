<?php

namespace App\Domain;

use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use Hashids\Hashids;
use App\Application\Payload\Payload;

abstract class ObjectWriter {

    protected $logger;
    protected $current_user;
    protected $mapper = null;

    public function __construct(LoggerInterface $logger, CurrentUser $user) {
        $this->logger = $logger;
        $this->current_user = $user;
    }

    protected function getMapper() {
        return $this->mapper;
    }

    protected function createEntry($data, $user = null) {
        // Remove CSRF attributes
        if (array_key_exists('csrf_name', $data)) {
            unset($data["csrf_name"]);
        }
        if (array_key_exists('csrf_value', $data)) {
            unset($data["csrf_value"]);
        }

        if (!isset($user)) {
            $data['user'] = $this->current_user->getUser()->id;
        } else {
            $data['user'] = $user;
            // use this user for filtering
            $this->getMapper()->setUser($user);
        }

        $dataobject = $this->getMapper()->getDataObject();
        $entry = new $dataobject($data);

        /**
         * Add users (for m:n)
         */
        if (array_key_exists("users", $data) && is_array($data["users"])) {
            $users = filter_var_array($data["users"], FILTER_SANITIZE_NUMBER_INT);
            $entry->setUsers($users);
        }

        return $entry;
    }

    protected function insertEntry($entry) {
        $id = $this->getMapper()->insert($entry);
        $this->addUsers($id, $entry);

        return $id;
    }

    protected function updateEntry($entry) {
        // try to access entry, maybe the user is not authorized, so this throws an exception (not found)
        $oldEntry = $this->getMapper()->get($entry->id);

        $elements_changed = $this->getMapper()->update($entry);

        $updated = $elements_changed > 0;

        if ($updated) {
            $this->addUsers($entry->id, $entry);
        }

        return $updated;
    }

    /**
     * Save m-n user table 
     */
    protected function addUsers($id, $entry) {
        if ($this->getMapper()->hasUserTable()) {
            $this->getMapper()->deleteUsers($id);

            if (!empty($entry->getUsers())) {
                $this->getMapper()->addUsers($id, $entry->getUsers());
            }
        }
    }

    public function save($id, $data, $additionalData = null): Payload {

        $for_user = null;
        if (isset($additionalData) && is_array($additionalData) && array_key_exists("user", $additionalData)) {
            $for_user = $additionalData["user"];
        }

        $entry = $this->createEntry($data, $for_user);

        if ($entry->hasParsingErrors()) {
            $this->logger->addError("Insert failed " . get_class($entry), array("message" => $this->translation->getTranslatedString($entry->getParsingErrors()[0])));
            return new Payload(Payload::$STATUS_PARSING_ERRORS, $entry);
        }

        // create
        if ($id == null) {
            $id = $this->insertEntry($entry);
            // get the created entry
            $entry = $this->getMapper()->get($id);

            $this->logger->addNotice("Insert Entry " . get_class($entry), array("id" => $entry->id));

            return new Payload(Payload::$STATUS_NEW, $entry, $data);
        }
        // update
        $update = $this->updateEntry($entry);
        if ($update) {
            $this->logger->addNotice("Update Entry " . get_class($entry), array("id" => $entry->id));
            return new Payload(Payload::$STATUS_UPDATE, $entry);
        } else {
            $this->logger->addNotice("No Update of Entry " . get_class($entry), array("id" => $entry->id));
            return new Payload(Payload::$STATUS_NO_UPDATE, $entry);
        }
        $this->logger->addError("Error while inserting entry " . get_class($entry), array("entry" => $entry));

        return new Payload(Payload::$STATUS_ERROR, $entry);
    }

    public function setHash($entry) {
        if (empty($entry->getHash())) {
            $hashids = new Hashids('', 10);
            $hash = $hashids->encode($entry->id);
            $this->getMapper()->setHash($entry->id, $hash);
        }
    }

}
