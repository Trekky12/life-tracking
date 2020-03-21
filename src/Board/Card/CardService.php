<?php

namespace App\Board\Card;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;
use App\Main\Helper;
use App\Board\Mapper as BoardMapper;
use App\Board\Stack\StackService;
use App\Board\Label\LabelService;
use App\User\UserService;

class CardService extends \App\Base\Service {

    protected $dataobject = \App\Board\Card\Card::class;
    protected $dataobject_parent = \App\Board\Stack\Stack::class;
    protected $element_view_route = 'boards_view';
    protected $module = "boards";
    private $board_mapper;
    private $stack_service;
    private $label_service;
    private $user_service;
    private $users_preSave = [];

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Helper $helper,
            Mapper $mapper,
            BoardMapper $board_mapper,
            StackService $stack_service,
            LabelService $label_service,
            UserService $user_service) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
        $this->helper = $helper;
        $this->board_mapper = $board_mapper;
        $this->stack_service = $stack_service;
        $this->label_service = $label_service;
        $this->user_service = $user_service;
    }

    protected function getParentObjectService() {
        return $this->stack_service;
    }

    protected function getAffectedUsers($entry) {
        $stack = $this->stack_service->getEntry($entry->stack);
        $board = $this->board_mapper->get($stack->board);
        return $this->getUsers($board->id);
    }

    protected function getElementViewRoute($entry) {
        $stack = $this->stack_service->getEntry($entry->stack);
        $board = $this->board_mapper->get($stack->board);
        $this->element_view_route_params["hash"] = $board->getHash();
        return parent::getElementViewRoute($entry);
    }

    public function hasAccess($id, $data = []) {
        $user = $this->current_user->getUser()->id;
        $this->users_preSave = array();

        if (!is_null($id)) {
            $user_cards = $this->mapper->getUserCards($user);
            if (!in_array($id, $user_cards)) {
                return false;
            }

            /**
             * Get users pre change
             */
            $this->users_preSave = $this->getUsers($id);
        } elseif (is_array($data)) {
            $user_stacks = $this->stack_service->getUserStacks($user);
            if (!array_key_exists("stack", $data) || !in_array($data["stack"], $user_stacks)) {
                return false;
            }
        }
        return true;
    }

    public function prepareCard($id, $entry) {
        $card_labels = $this->label_service->getLabelsFromCard($id);
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

        return $entry;
    }

    public function archive($id, $archive) {
        $user = $this->current_user->getUser()->id;
        return $this->mapper->setArchive($id, $archive, $user);
    }

    public function changePosition($cards) {
        $user = $this->current_user->getUser()->id;
        $user_stacks = $this->stack_service->getUserStacks($user);

        foreach ($cards as $position => $item) {
            if (in_array($item, $user_stacks)) {
                $this->mapper->updatePosition($item, $position, $user);
            }
        }
    }

    public function moveCard($stack, $card) {
        $user = $this->current_user->getUser()->id;
        $user_cards = $this->mapper->getUserCards($user);
        $user_stacks = $this->stack_service->getUserStacks($user);

        if (!is_null($stack) && !is_null($card) && in_array($stack, $user_stacks) && in_array($card, $user_cards)) {
            $this->mapper->moveCard($card, $stack, $user);

            return true;
        }

        return false;
    }

    public function deleteLabelsFromCard($id) {
        $this->label_service->deleteLabelsFromCard($id);
    }

    public function addLabelsToCard($id, $labels) {

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
            $this->logger->addError("After Card Save", array("data" => $id, "error" => $e->getMessage()));
        }
    }

    public function getCardsFromStack($stack_id, $show_archive) {
        return $this->mapper->getCardsFromStack($stack_id, $show_archive);
    }

    public function getCardsUser() {
        return $this->mapper->getCardsUser();
    }

    public function notifyUsers($id) {

        $board_id = $this->mapper->getCardBoard($id);

        $my_user_id = intval($this->current_user->getUser()->id);
        $users_afterSave = $this->getUsers($id);
        $new_users = array_diff($users_afterSave, $this->users_preSave);
        $users = $this->user_service->getAll();

        $board = $this->board_mapper->get($board_id, false);
        $card = $this->mapper->get($id);

        $stack = $this->stack_service->getEntry($card->stack);

        $subject = $this->translation->getTranslatedString('MAIL_ADDED_TO_CARD');

        foreach ($new_users as $nu) {

            // except self
            if ($nu !== $my_user_id) {
                $user = $users[$nu];

                if ($user->mail && $user->mails_board == 1) {

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

                    $this->helper->send_mail('mail/general.twig', $user->mail, $subject, $variables);
                }
            }
        }
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
