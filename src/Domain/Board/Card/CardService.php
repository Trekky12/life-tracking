<?php

namespace App\Domain\Board\Card;

use App\Domain\GeneralService;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Helper;
use App\Domain\Board\BoardMapper;
use App\Domain\Board\Stack\StackService;
use App\Domain\Board\Label\LabelService;
use App\Domain\User\UserService;
use App\Application\Payload\Payload;

class CardService extends GeneralService {

    private $board_mapper;
    private $stack_service;
    private $label_service;
    private $user_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, CardMapper $mapper, BoardMapper $board_mapper, StackService $stack_service, LabelService $label_service, UserService $user_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        //$this->helper = $helper;
        $this->board_mapper = $board_mapper;
        $this->stack_service = $stack_service;
        $this->label_service = $label_service;
        $this->user_service = $user_service;
    }

    public function hasAccess($id, $data = []) {
        $user = $this->current_user->getUser()->id;

        if (!is_null($id)) {
            $user_cards = $this->mapper->getUserCards($user);
            if (!in_array($id, $user_cards)) {
                return false;
            }
        } elseif (is_array($data)) {
            $user_stacks = $this->stack_service->getUserStacks($user);
            if (!array_key_exists("stack", $data) || !in_array($data["stack"], $user_stacks)) {
                return false;
            }
        }
        return true;
    }

    public function archive($entry_id, $data) {

        if (!$this->hasAccess($entry_id, [])) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        if (array_key_exists("archive", $data) && in_array($data["archive"], array(0, 1))) {

            $user = $this->current_user->getUser()->id;
            $is_archived = $this->mapper->setArchive($entry_id, $data["archive"], $user);

            $response_data = ['is_archived' => $is_archived];
            return new Payload(Payload::$RESULT_JSON, $response_data);
        } else {
            $response_data = ['status' => 'error', "error" => "missing data"];
            return new Payload(Payload::$RESULT_JSON, $response_data);
        }
    }

    public function moveCard($data) {

        $stack = array_key_exists("stack", $data) && !empty($data["stack"]) ? filter_var($data['stack'], FILTER_SANITIZE_NUMBER_INT) : null;
        $card = array_key_exists("card", $data) && !empty($data["card"]) ? filter_var($data['card'], FILTER_SANITIZE_NUMBER_INT) : null;

        $user = $this->current_user->getUser()->id;
        $user_cards = $this->mapper->getUserCards($user);
        $user_stacks = $this->stack_service->getUserStacks($user);

        $response_data = ['status' => 'error'];
        if (!is_null($stack) && !is_null($card) && in_array($stack, $user_stacks) && in_array($card, $user_cards)) {
            $this->mapper->moveCard($card, $stack, $user);

            $response_data = ['status' => 'success'];
        }

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function updatePosition($data) {

        if (array_key_exists("card", $data) && !empty($data["card"])) {

            $cards = filter_var_array($data["card"], FILTER_SANITIZE_NUMBER_INT);

            $user = $this->current_user->getUser()->id;
            $user_cards = $this->mapper->getUserCards($user);

            foreach ($cards as $position => $item) {
                if (in_array($item, $user_cards)) {
                    $this->mapper->updatePosition($item, $position, $user);
                }
            }

            $response_data = ['status' => 'success'];
            return new Payload(Payload::$RESULT_JSON, $response_data);
        }
        $response_data = ['status' => 'error'];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function getCardsFromStack($stack_id, $show_archive) {
        return $this->mapper->getCardsFromStack($stack_id, $show_archive);
    }

    public function getCardsUser() {
        return $this->mapper->getCardsUser();
    }

    public function getData($entry_id) {
        if (!$this->hasAccess($entry_id, [])) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        $entry = $this->getEntry($entry_id);

        // append card labels and usernames to output
        $card_labels = $this->label_service->getLabelsFromCard($entry_id);
        $entry->labels = $card_labels;

        $users = $this->user_service->getAll();

        if ($entry->createdBy) {
            $entry->createdBy = $users[$entry->createdBy]->name;
        }
        if ($entry->changedBy) {
            $entry->changedBy = $users[$entry->changedBy]->name;
        }
        if ($entry->description) {
            $entry->description = html_entity_decode(htmlspecialchars_decode($entry->description));
        }
        if ($entry->title) {
            $entry->title = htmlspecialchars_decode($entry->title);
        }

        $response_data = ['entry' => $entry];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function sendReminder() {

        $due_cards = $this->mapper->getCardReminder();
        $users = $this->user_service->getAll();
        $stacks = $this->stack_service->getAll();

        $subject = $this->translation->getTranslatedString('MAIL_CARD_REMINDER');

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

        $fmt = new \IntlDateFormatter($language, NULL, NULL);
        $fmt->setPattern($dateFormatPHP['month_name_full']);


        foreach ($due_cards as $user_id => $cards) {
            $user = $users[$user_id];

            if ($user->mail && $user->mails_board_reminder == 1) {
                $variables = array(
                    'header' => '',
                    'subject' => $subject,
                    'headline' => sprintf($this->translation->getTranslatedString('HELLO') . ' %s', $user->name),
                    'content' => $this->translation->getTranslatedString('MAIL_CARD_REMINDER_DETAIL')
                );

                $user_cards = $due_cards[$user_id];

                $mail_content = '';

                foreach ($user_cards as $today => $stacks) {

                    if ($today == 1) {
                        $mail_content .= '<h2>' . $this->translation->getTranslatedString('TODAY') . ':</h2>';
                    } else {
                        $mail_content .= '<h2>' . $this->translation->getTranslatedString('OVERDUE') . ':</h2>';
                    }
                    foreach ($stacks as $board_name => $board) {
                        $url = $this->helper->getBaseURL() . $this->router->urlFor('boards_view', array('hash' => $board["hash"]));
                        $mail_content .= '<h3><a href="' . $url . '">Board: ' . $board_name . '</a></h3>';
                        $mail_content .= '<ul>';

                        foreach ($board["stacks"] as $stack_name => $cards) {
                            $mail_content .= '<li>' . $stack_name;
                            $mail_content .= '  <ul>';
                            $mail_content .= implode('', array_map(function($c) use ($fmt, $today) {
                                        $output = '<li>' . $c["title"];
                                        if ($today != 1) {
                                            $dateObj = new \DateTime($c["date"]);
                                            $output .= ' (' . $fmt->format($dateObj) . ')';
                                        }
                                        $output .= '</li>';
                                        return $output;
                                    }, $cards));
                            $mail_content .= '  </ul>';
                            $mail_content .= '</li>';
                        }
                        $mail_content .= '</ul>';
                    }
                }

                $variables['extra'] = $mail_content;

                if (!empty($mail_content)) {
                    $this->helper->send_mail('mail/general.twig', $user->mail, $subject, $variables);
                }
            }
        }
        return true;
    }

}
