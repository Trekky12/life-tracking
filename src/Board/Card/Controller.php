<?php

namespace App\Board\Card;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Main\Helper;
use App\Activity\Controller as Activity;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Board\Card\Card';
    protected $parent_model = '\App\Board\Stack\Stack';
    protected $element_view_route = 'boards_view';
    protected $module = "boards";
    private $board_mapper;
    private $stack_mapper;
    private $label_mapper;
    private $users_preSave = array();
    private $users_afterSave = array();

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation, CurrentUser $current_user) {
        parent::__construct($logger, $twig, $helper, $flash, $router, $settings, $db, $activity, $translation, $current_user);

        $this->mapper = new Mapper($this->db, $this->translation, $current_user);
        $this->board_mapper = new \App\Board\Mapper($this->db, $this->translation, $current_user);
        $this->stack_mapper = new \App\Board\Stack\Mapper($this->db, $this->translation, $current_user);
        $this->label_mapper = new \App\Board\Label\Mapper($this->db, $this->translation, $current_user);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, array &$data, Request $request) {
        $user = $this->current_user->getUser()->id;
        $this->users_preSave = array();

        if (!is_null($id)) {
            $user_cards = $this->board_mapper->getUserCards($user);
            if (!in_array($id, $user_cards)) {
                throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
            }

            /**
             * Get users pre change
             */
            $this->users_preSave = $this->mapper->getUsers($id);
        } elseif (is_array($data)) {
            $user_stacks = $this->board_mapper->getUserStacks($user);
            if (!array_key_exists("stack", $data) || !in_array($data["stack"], $user_stacks)) {
                throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

    /**
     * Save labels, notify user
     */
    protected function afterSave($id, array $data, Request $request) {
        // card check is already done in preSave()
        $board_id = $this->mapper->getCardBoard($id);

        try {
            // remove old labels
            $this->label_mapper->deleteLabelsFromCard($id);

            // add new labels
            if (array_key_exists("labels", $data) && is_array($data["labels"])) {
                $labels = filter_var_array($data["labels"], FILTER_SANITIZE_NUMBER_INT);

                // check if label is on this board
                $board_labels = $this->label_mapper->getLabelsFromBoard($board_id);
                $board_labels_ids = array_map(function($label) {
                    return $label->id;
                }, $board_labels);

                // Only add labels of this board
                $filtered_labels = array_filter($labels, function($label) use($board_labels_ids) {
                    return in_array($label, $board_labels_ids);
                });

                $this->label_mapper->addLabelsToCard($id, $filtered_labels);
            }
        } catch (\Exception $e) {
            $this->logger->addError("After Card Save", array("data" => $id, "error" => $e->getMessage()));
        }

        /**
         * Notify changed users
         */
        $my_user_id = intval($this->current_user->getUser()->id);
        $this->users_afterSave = $this->mapper->getUsers($id);
        $new_users = array_diff($this->users_afterSave, $this->users_preSave);
        $users = $this->user_mapper->getAll();

        $board = $this->board_mapper->get($board_id, false);
        $card = $this->mapper->get($id);

        $stack = $this->stack_mapper->get($card->stack);

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

    /**
     * append card labels and usernames to output
     * @param type $id
     * @param type $entry
     * @return type
     */
    protected function afterGetAPI($id, $entry, Request $request) {
        $card_labels = $this->label_mapper->getLabelsFromCard($id);
        $entry->labels = $card_labels;

        $users = $this->user_mapper->getAll();
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

    public function updatePosition(Request $request, Response $response) {
        $data = $request->getParsedBody();

        try {

            $user = $this->current_user->getUser()->id;
            $user_cards = $this->board_mapper->getUserCards($user);

            if (array_key_exists("card", $data) && !empty($data["card"])) {

                foreach ($data['card'] as $position => $item) {
                    if (in_array($item, $user_cards)) {
                        $this->mapper->updatePosition($item, $position, $user);
                    }
                }
                $response_data = ['status' => 'success'];
                return $response->withJSON($response_data);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Update Card Position", array("data" => $data, "error" => $e->getMessage()));

            $response_data = ['status' => 'error', "error" => $e->getMessage()];
            return $response->withJSON($response_data);
        }

        $response_data = ['status' => 'error'];
        return $response->withJSON($response_data);
    }

    public function moveCard(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $stack = array_key_exists("stack", $data) && !empty($data["stack"]) ? filter_var($data['stack'], FILTER_SANITIZE_NUMBER_INT) : null;
        $card = array_key_exists("card", $data) && !empty($data["card"]) ? filter_var($data['card'], FILTER_SANITIZE_NUMBER_INT) : null;

        try {
            $user = $this->current_user->getUser()->id;
            $user_cards = $this->board_mapper->getUserCards($user);
            $user_stacks = $this->board_mapper->getUserStacks($user);

            if (!is_null($stack) && !is_null($card) && in_array($stack, $user_stacks) && in_array($card, $user_cards)) {
                $this->mapper->moveCard($card, $stack, $user);

                $response_data = ['status' => 'success'];
                return $response->withJSON($response_data);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Move Card", array("data" => $data, "error" => $e->getMessage()));

            $response_data = ['status' => 'error', "error" => $e->getMessage()];
            return $response->withJSON($response_data);
        }

        $response_data = ['status' => 'error'];
        return $response->withJSON($response_data);
    }

    public function archive(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $id = $request->getAttribute('id');
        try {
            $this->preSave($id, $data, $request);

            if (array_key_exists("archive", $data) && in_array($data["archive"], array(0, 1))) {
                $user = $this->current_user->getUser()->id;
                $is_archived = $this->mapper->setArchive($id, $data["archive"], $user);

                $response_data = ['is_archived' => $is_archived];
                return $response->withJson($response_data);
            } else {
                $response_data = ['status' => 'error', "error" => "missing data"];
                return $response->withJSON($response_data);
            }
            $response_data = ['is_archived' => $is_archived];
            return $response->withJson($response_data);
        } catch (\Exception $e) {
            $this->logger->addError("Archive Card", array("data" => $data, "id" => $id, "error" => $e->getMessage()));

            $response_data = ['status' => 'error', "error" => $e->getMessage()];
            return $response->withJSON($response_data);
        }
    }

    public function reminder() {

        $due_cards = $this->mapper->getCardReminder();
        $users = $this->user_mapper->getAll();
        $stacks = $this->stack_mapper->getAll();

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

    protected function preDelete($id, Request $request) {
        $user = $this->current_user->getUser()->id;
        $user_cards = $this->board_mapper->getUserCards($user);
        if (!in_array($id, $user_cards)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }
    }

    protected function getParentObjectMapper() {
        return $this->stack_mapper;
    }

    protected function getAffectedUsers($entry) {
        $stack = $this->stack_mapper->get($entry->stack);
        $board = $this->board_mapper->get($stack->board);
        return $this->board_mapper->getUsers($board->id);
    }

    protected function getElementViewRoute($entry) {
        $stack = $this->stack_mapper->get($entry->stack);
        $board = $this->board_mapper->get($stack->board);
        $this->element_view_route_params["hash"] = $board->getHash();
        return parent::getElementViewRoute($entry);
    }

}
