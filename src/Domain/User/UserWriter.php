<?php

namespace App\Domain\User;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Main\Helper;
use App\Domain\Main\Translator;

class UserWriter extends ObjectActivityWriter {
    
    private $helper;
    private $translation;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, UserMapper $mapper, Helper $helper, Translator $translation) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->helper = $helper;
        $this->translation = $translation;
    }

    public function save($id, $data, $additionalData = null): Payload {
        $payload = parent::save($id, $data, $additionalData);

        // notify new user
        // is new user?
        if ($payload->getStatus() == Payload::$STATUS_NEW) {
            $user = $payload->getResult();
            $data = $payload->getAdditionalData();
            $this->sendNewUserNotificationMail($user, $data);
        }
        return $payload;
    }
    
    private function sendNewUserNotificationMail($user, $data) {
        if ($user->mail && $user->mails_user == 1) {

            $subject = sprintf($this->translation->getTranslatedString('MAIL_YOUR_USER_ACCOUNT_AT'), $this->helper->getBaseURL());

            $variables = array(
                'header' => '',
                'subject' => $subject,
                'headline' => sprintf($this->translation->getTranslatedString('HELLO') . ' %s', $user->name),
                'content' => sprintf($this->translation->getTranslatedString('MAIL_USER_ACCOUNT_CREATED'), $this->helper->getBaseURL(), $this->helper->getBaseURL())
                . '<br/>&nbsp;<br/>&nbsp;'
                . sprintf($this->translation->getTranslatedString('MAIL_YOUR_USERNAME'), $user->login)
            );

            if (array_key_exists("set_password", $data)) {
                $variables["content"] .= '<br/>&nbsp;' . sprintf($this->translation->getTranslatedString('MAIL_YOUR_PASSWORD'), $data["set_password"]);
            }

            if ($user->force_pw_change == 1) {
                $variables["content"] .= '<br/>&nbsp;<br/>&nbsp;' . $this->translation->getTranslatedString('MAIL_FORCE_CHANGE_PASSWORD');
            }
            $this->logger->addInfo("Notify new user via mail", ["subject" => $subject, "var" => $variables]);

            $this->helper->send_mail('mail/general.twig', $user->mail, $subject, $variables);
        }
    }

    public function getObjectViewRoute(): string {
        return 'users_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "general";
    }

}
