<?php

namespace App\Domain;

use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

abstract class ObjectRemover {

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

    public function delete($id, $user = null) {

        $error = null;
        try {
            // use this user for filtering
            if (isset($user)) {
                $this->mapper->setUser($user);
            }
            $is_deleted = $this->deleteEntry($id, $user);

            if ($is_deleted) {
                $this->logger->addNotice("Delete successfully " . $this->getMapper()->getDataObject(), array("id" => $id));
                return new Payload(Payload::$STATUS_DELETE_SUCCESS, $error);
            } else {
                $this->logger->addError("Delete failed " . $this->getMapper()->getDataObject(), array("id" => $id));
                return new Payload(Payload::$STATUS_DELETE_ERROR, $error);
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $this->logger->addError("Delete failed " . $this->getMapper()->getDataObject(), array("id" => $id, "error" => $e->getMessage()));
        }
        return new Payload(Payload::$STATUS_ERROR, $error);
    }

    protected function deleteEntry($id) {
        return $this->mapper->delete($id);
    }

}
