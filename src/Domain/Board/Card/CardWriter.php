<?php

namespace App\Domain\Board\Card;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Domain\Board\BoardMapper;
use App\Domain\Board\Stack\StackService;
use App\Domain\Board\Label\LabelService;
use App\Domain\User\UserService;
use App\Domain\Main\Helper;
use App\Domain\Main\Translator;
use App\Domain\Base\Settings;
use Slim\Routing\RouteParser;
use App\Application\Payload\Payload;
use App\Domain\Notifications\NotificationsService;

class CardWriter extends ObjectActivityWriter {

    private $card_service;
    private $stack_service;
    private $label_service;
    private $board_mapper;
    private $user_service;
    private $helper;
    private $translation;
    private $settings;
    private $router;
    private $notification_service;

    public function __construct(LoggerInterface $logger, 
            CurrentUser $user, 
            ActivityCreator $activity,
            CardMapper $mapper, 
            CardService $card_service, 
            StackService $stack_service, 
            LabelService $label_service, 
            BoardMapper $board_mapper,
            UserService $user_service,
            Helper $helper,
            Translator $translation,
            Settings $settings,
            RouteParser $router,
            NotificationsService $notification_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->card_service = $card_service;
        $this->stack_service = $stack_service;
        $this->label_service = $label_service;
        $this->board_mapper = $board_mapper;
        $this->user_service = $user_service;
        $this->helper = $helper;
        $this->translation = $translation;
        $this->settings = $settings;
        $this->router = $router;
        $this->notification_service = $notification_service;
    }

    public function save($id, $data, $additionalData = null): Payload {
        if (!$this->card_service->hasAccess($id, $data)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        
        $users_preSave = $this->mapper->getUsers($id);
        
        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        // remove old labels
        $this->label_service->deleteLabelsFromCard($id);

        if (array_key_exists("labels", $data) && is_array($data["labels"])) {
            $labels = filter_var_array($data["labels"], FILTER_SANITIZE_NUMBER_INT);
            // save new labels
            $this->addLabelsToCard($entry->id, $labels);
        }

        /**
         * Notify changed users
         */
        $this->notifyUsers($entry, $users_preSave);

        $entry->users = $this->mapper->getUsers($entry->id, true);
        $entry->labels = $this->label_service->getLabelsFromCard($entry->id);

        $payload = $payload->withEntry($entry);

        return $payload;
    }

    private function addLabelsToCard($id, $labels) {

        $board_id = $this->mapper->getCardBoard($id);

        try {
            // check if label is on this board
            $board_labels = $this->label_service->getLabelsFromBoard($board_id);
            $board_labels_ids = array_map(function($label) {
                return $label->id;
            }, $board_labels);

            // Only add labels of this board
            $filtered_labels = array_filter($labels, function($label) use($board_labels_ids) {
                return in_array($label, $board_labels_ids);
            });

            $this->label_service->addLabelsToCard($id, $filtered_labels);
        } catch (\Exception $e) {
            $this->logger->error("After Card Save", array("data" => $id, "error" => $e->getMessage()));
        }
    }
    
    private function notifyUsers($card, $users_preSave) {

        $board_id = $this->mapper->getCardBoard($card->id);

        $my_user_id = intval($this->current_user->getUser()->id);
        $users_afterSave = $this->mapper->getUsers($card->id);
        $new_users = array_diff($users_afterSave, $users_preSave);
        $users = $this->user_service->getAll();

        $board = $this->board_mapper->get($board_id, false);
        $stack = $this->stack_service->getEntry($card->stack);

        $subject = $this->translation->getTranslatedString('MAIL_ADDED_TO_CARD');

        foreach ($new_users as $nu => $login) {

            // except self
            if ($nu !== $my_user_id) {
                $user = $users[$nu];

                if ($user->mail) {

                    $variables = array(
                        'header' => '',
                        'subject' => $subject,
                        'headline' => sprintf($this->translation->getTranslatedString('HELLO') . ' %s', $user->name),
                        'content' => sprintf($this->translation->getTranslatedString('MAIL_ADDED_TO_CARD_DETAIL'), $this->helper->getBaseURL() . $this->router->urlFor('boards_view', array('hash' => $board->getHash())), $board->name, $stack->name, $card->title),
                        'extra' => ''
                    );

                    if ($card->description) {
                        //$description = nl2br($card->description);
                        $parser = new \Michelf\Markdown();
                        //$parser->hard_wrap  = true;
                        $description = $parser->transform(str_replace("\n", "\n\n", $card->description));
                        $variables["extra"] .= '<h2>' . $this->translation->getTranslatedString('DESCRIPTION') . ':</h2><div id="description">' . $description . '</div>';
                    }
                    if ($card->date) {
                        $language = $this->settings->getAppSettings()['i18n']['php'];
                        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

                        $fmt = new \IntlDateFormatter($language, NULL, NULL);
                        $fmt->setPattern($dateFormatPHP['month_name_full']);

                        $dateObj = new \DateTime($card->date);
                        $variables["extra"] .= '<h2>' . $this->translation->getTranslatedString('DATE') . ':</h2>' . $fmt->format($dateObj) . '';
                    }
                    if ($card->time) {
                        $variables["extra"] .= '<h2>' . $this->translation->getTranslatedString('TIME') . ':</h2>' . $card->time . '';
                    }

                    //$this->helper->send_mail('mail/general.twig', $user->mail, $subject, $variables);
                    $this->notification_service->sendMailNotificationToUserWithCategory($user, "MAIL_CATEGORY_BOARDS_CARD_ADD", 'mail/general.twig', $subject, $variables, $board->id);
                }
                
                // Notification
                $content = sprintf($this->translation->getTranslatedString('NOTIFICATION_ADDED_TO_CARD'), $board->name, $stack->name, $card->title);
                $path = $this->router->urlFor('boards_view', array('hash' => $board->getHash()));
                $this->notification_service->sendNotificationsToUserWithCategory($user->id, "NOTIFICATION_CATEGORY_BOARDS_CARD_ADD", $subject, $content, $path, $board->id);
            }
        }
    }

    public function getParentMapper() {
        return $this->board_mapper;
    }
    
    public function getParentID($entry): int {
        return $this->mapper->getCardBoard($entry->id);
    }

    public function getObjectViewRoute(): string {
        return 'boards_view';
    }

    public function getObjectViewRouteParams($entry): array {
        $board_id = $this->mapper->getCardBoard($entry->id);
        $board = $this->board_mapper->get($board_id);
        return ["hash" => $board->getHash()];
    }

    public function getModule(): string {
        return "boards";
    }

}
