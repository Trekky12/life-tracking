<?php

namespace App\Domain\Board;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Helper;
use App\Domain\Main\Translator;
use App\Domain\User\UserService;
use App\Application\Payload\Payload;

class BoardWriter extends ObjectActivityWriter {
    
    private $board_service;
    private $translation;
    private $helper;
    private $user_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, BoardMapper $mapper, BoardService $board_service, Translator $translation, Helper $helper, UserService $user_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->board_service = $board_service;
        $this->translation = $translation;
        $this->helper = $helper;
        $this->user_service = $user_service;
    }

    public function save($id, $data, $user = null): Payload {
        
        $users_preSave = $this->board_service->getUsers($id);
        if ($this->board_service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        
        $payload = parent::save($id, $data, $user);
        $entry = $payload->getResult();

        $this->setHash($entry);
        $this->notifyUsers($entry, $users_preSave);
        
        return $payload;
    }
    
    private function notifyUsers($entry, $users_preSave) {
        /**
         * Notify new users
         */
        $my_user_id = intval($this->current_user->getUser()->id);
        $users_afterSave = $this->board_service->getUsers($entry->id);
        $new_users = array_diff($users_afterSave, $users_preSave);

        $subject = $this->translation->getTranslatedString('MAIL_ADDED_TO_BOARD');

        foreach ($new_users as $nu) {

            // except self
            if ($nu !== $my_user_id) {
                $user = $this->user_service->getEntry($nu);

                if ($user->mail && $user->mails_board == 1) {

                    $variables = array(
                        'header' => '',
                        'subject' => $subject,
                        'headline' => sprintf($this->translation->getTranslatedString('HELLO') . ' %s', $user->name),
                        'content' => sprintf($this->translation->getTranslatedString('MAIL_ADDED_TO_BOARD_DETAIL'), $this->helper->getBaseURL() . $this->router->urlFor('boards_view', array('hash' => $board->getHash())), $board->name)
                    );

                    $this->helper->send_mail('mail/general.twig', $user->mail, $subject, $variables);
                }
            }
        }
    }

    public function getObjectViewRoute(): string {
        return 'boards_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "boards";
    }

}
