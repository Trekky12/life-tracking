<?php

namespace App\Domain\Base;

use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Domain\Main\Translator;

abstract class Controller {

    protected $logger;
    protected $flash;
    protected $translation;
    protected $service;

    public function __construct(LoggerInterface $logger, Flash $flash, Translator $translation) {
        $this->logger = $logger;
        $this->flash = $flash;
        $this->translation = $translation;
    }

    protected function doSave($id, $data, $user) {

        $entry = $this->service->createEntry($data, $user);

        if ($entry->hasParsingErrors()) {
            $this->flash->addMessage('message', $this->translation->getTranslatedString($entry->getParsingErrors()[0]));
            $this->flash->addMessage('message_type', 'danger');

            $this->logger->addError("Insert failed " . $this->service->getDataObject(), array("message" => $this->translation->getTranslatedString($entry->getParsingErrors()[0])));
        } else {
            // create
            if ($id == null) {
                $id = $this->service->insertEntry($entry);

                $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_SUCCESS_ADD"));
                $this->flash->addMessage('message_type', 'success');
                $this->logger->addNotice("Insert Entry " . $this->service->getDataObject(), array("id" => $id));
            }
            // update
            else {
                $update = $this->service->updateEntry($entry);

                if ($update) {
                    $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_SUCCESS_UPDATE"));
                    $this->flash->addMessage('message_type', 'success');

                    $this->logger->addNotice("Update Entry " . $this->service->getDataObject(), array("id" => $id));
                } else {
                    $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_NOT_CHANGED"));
                    $this->flash->addMessage('message_type', 'info');

                    $this->logger->addNotice("No Update of Entry " . $this->service->getDataObject(), array("id" => $id));
                }
            }
        }
        return $id;
    }

    protected function doDelete($id, $user = null) {
        $response_data = ['is_deleted' => false, 'error' => ''];

        try {

            $is_deleted = $this->service->deleteEntry($id, $user);

            $response_data['is_deleted'] = $is_deleted;
            if ($is_deleted) {
                $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_SUCCESS_DELETE"));
                $this->flash->addMessage('message_type', 'success');

                $this->logger->addNotice("Delete successfully " . $this->service->getDataObject(), array("id" => $id));
            } else {
                $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_ERROR_DELETE"));
                $this->flash->addMessage('message_type', 'danger');

                $this->logger->addError("Delete failed " . $this->service->getDataObject(), array("id" => $id));
            }
        } catch (\Exception $e) {
            $response_data['error'] = $e->getMessage();
            $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_ERROR_DELETE"));
            $this->flash->addMessage('message_type', 'danger');

            $this->logger->addError("Delete failed " . $this->service->getDataObject(), array("id" => $id, "error" => $e->getMessage()));
        }

        return $response_data;
    }

}
