<?php

namespace App\Domain\Finances\Recurring;

use Psr\Log\LoggerInterface;
use App\Domain\Finances\FinancesEntry;
use App\Domain\Notifications\NotificationsService;
use Slim\Routing\RouteParser;
use App\Domain\Main\Translator;
use App\Application\Payload\Payload;

class RecurringFinanceEntryCreator {

    private $logger;
    private $mapper;
    private $finances_entry_writer;
    private $notification_service;
    private $router;
    private $translation;

    public function __construct(LoggerInterface $logger, RecurringMapper $mapper, RecurringFinanceEntryWriter $finances_entry_writer, NotificationsService $notification_service, RouteParser $router, Translator $translation) {
        $this->logger = $logger;
        $this->mapper = $mapper;
        $this->finances_entry_writer = $finances_entry_writer;
        $this->notification_service = $notification_service;
        $this->router = $router;
        $this->translation = $translation;
    }

    public function update() {

        $mentries = $this->mapper->getRecurringEntries();

        if ($mentries) {
            $this->logger->debug('Recurring Entries', $mentries);

            foreach ($mentries as $mentry) {
                $this->createElement($mentry);
            }

            $mentry_ids = array_map(function($el) {
                return $el->id;
            }, $mentries);
            $this->mapper->updateLastRun($mentry_ids);
        }

        return true;
    }

    public function createEntry($id) {
        $mentry = $this->mapper->get($id);

        $entry_id = $this->createElement($mentry);
        
        return new Payload(Payload::$STATUS_NEW, $mentry);
    }

    private function createElement($mentry) {
        $entry = new FinancesEntry([
            'type' => $mentry->type,
            'category' => $mentry->category,
            'description' => $mentry->description,
            'value' => $mentry->value,
            'common' => $mentry->common,
            'common_value' => $mentry->common_value,
            'notice' => $mentry->notice,
            'user' => $mentry->user,
            'fixed' => 1,
            'paymethod' => $mentry->paymethod
        ]);
        $entry_id =  $this->finances_entry_writer->addFinanceEntry($entry);
        
        // Notification
        $subject = $this->translation->getTranslatedString('NOTIFICATION_FINANCES_RECURRING_ADDED_SUBJECT');
        $content = sprintf($this->translation->getTranslatedString('NOTIFICATION_FINANCES_RECURRING_ADDED_CONTENT'), $mentry->description);
        $entry_path = $this->router->urlFor('finances_edit', array('id' => $entry_id));
        
        $this->notification_service->sendNotificationsToUserWithCategory($mentry->user, "NOTIFICATION_CATEGORY_FINANCES_RECURRING", $subject, $content, $entry_path);

        return $entry_id;
    }

}
