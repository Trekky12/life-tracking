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

    public function delete($id, $additionalData = null) {

        $error = null;
        try {

            // try to access entry, maybe the user is not authorized, so this throws an exception (not found)
            $oldEntry = $this->getMapper()->get($id);

            $is_deleted = $this->deleteEntry($id);

            if ($is_deleted) {
                $this->logger->notice("Delete successfully " . $this->getMapper()->getDataObject(), array("id" => $id));
                return new Payload(Payload::$STATUS_DELETE_SUCCESS, $error);
            } else {
                $this->logger->error("Delete failed " . $this->getMapper()->getDataObject(), array("id" => $id));
                return new Payload(Payload::$STATUS_DELETE_ERROR, $error);
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $this->logger->error("Delete failed " . $this->getMapper()->getDataObject(), array("id" => $id, "error" => $e->getMessage()));
        }
        return new Payload(Payload::$STATUS_ERROR, $error);
    }

    protected function deleteEntry($id) {
        return $this->mapper->delete($id);
    }

}
