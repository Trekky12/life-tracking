<?php

namespace App\Board\Card;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    private $users_preSave = array();
    private $users_afterSave = array();

    public function init() {
        $this->model = '\App\Board\Card\Card';

        $this->mapper = new Mapper($this->ci);
        $this->board_mapper = new \App\Board\Mapper($this->ci);
        $this->stack_mapper = new \App\Board\Stack\Mapper($this->ci);
        $this->label_mapper = new \App\Board\Label\Mapper($this->ci);
        $this->user_mapper = new \App\User\Mapper($this->ci);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, &$data) {
        $user = $this->ci->get('helper')->getUser()->id;
        $this->users_preSave = array();

        if (!is_null($id)) {
            $user_cards = $this->board_mapper->getUserCards($user);
            if (!in_array($id, $user_cards)) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }

            /**
             * Get users pre change
             */
            $this->users_preSave = $this->mapper->getUsers($id);
        } elseif (is_array($data)) {
            $user_stacks = $this->board_mapper->getUserStacks($user);
            if (!array_key_exists("stack", $data) || !in_array($data["stack"], $user_stacks)) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

    /**
     * Save labels, notify user
     */
    protected function afterSave($id, $data) {
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
            $logger = $this->ci->get('logger');
            $logger->addError("After Card Save", array("data" => $id, "error" => $e->getMessage()));
        }

        /**
         * Notify changed users
         */
        $my_user_id = intval($this->ci->get('helper')->getUser()->id);
        $this->users_afterSave = $this->mapper->getUsers($id);
        $new_users = array_diff($this->users_afterSave, $this->users_preSave);
        $users = $this->user_mapper->getAll();

        $board = $this->board_mapper->get($board_id, false);
        $card = $this->mapper->get($id);

        $stack = $this->stack_mapper->get($card->stack);

        $subject = $this->ci->get('helper')->getTranslatedString('MAIL_ADDED_TO_CARD');

        foreach ($new_users as $nu) {

            // except self
            if ($nu !== $my_user_id) {
                $user = $users[$nu];

                if ($user->mail && $user->mails_board == 1) {

                    $variables = array(
                        'header' => '',
                        'subject' => $subject,
                        'headline' => sprintf($this->ci->get('helper')->getTranslatedString('HELLO') . ' %s', $user->name),
                        'content' => sprintf($this->ci->get('helper')->getTranslatedString('MAIL_ADDED_TO_CARD_DETAIL'), $this->ci->get('helper')->getPath() . $this->ci->get('router')->pathFor('boards_view', array('hash' => $board->hash)), $board->name, $stack->name, $card->title),
                        'extra' => ''
                    );

                    if ($card->description) {
                        //$description = nl2br($card->description);
                        $parser = new \Michelf\Markdown();
                        //$parser->hard_wrap  = true;
                        $description = $parser->transform(str_replace("\n", "\n\n", $card->description));
                        $variables["extra"] .= '<h2>' . $this->ci->get('helper')->getTranslatedString('DESCRIPTION') . ':</h2><div id="description">' . $description . '</div>';
                    }
                    if ($card->date) {
                        $langugage = $this->ci->get('settings')['app']['i18n']['php'];
                        $dateFormatPHP = $this->ci->get('settings')['app']['i18n']['dateformatPHP'];
                        
                        $fmt = new \IntlDateFormatter($langugage, NULL, NULL);
                        $fmt->setPattern($dateFormatPHP['month_name_full']);

                        $dateObj = new \DateTime($card->date);
                        $variables["extra"] .= '<h2>' . $this->ci->get('helper')->getTranslatedString('DATE') . ':</h2>' . $fmt->format($dateObj) . '';
                    }
                    if ($card->time) {
                        $variables["extra"] .= '<h2>' . $this->ci->get('helper')->getTranslatedString('TIME') . ':</h2>' . $card->time . '';
                    }

                    $this->ci->get('helper')->send_mail('mail/general.twig', $user->mail, $subject, $variables);
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
    protected function afterGetAPI($id, $entry) {
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

            $user = $this->ci->get('helper')->getUser()->id;
            $user_cards = $this->board_mapper->getUserCards($user);

            if (array_key_exists("card", $data) && !empty($data["card"])) {

                foreach ($data['card'] as $position => $item) {
                    if (in_array($item, $user_cards)) {
                        $this->mapper->updatePosition($item, $position, $user);
                    }
                }
                return $response->withJSON(array('status' => 'success'));
            }
        } catch (\Exception $e) {
            $logger = $this->ci->get('logger');
            $logger->addError("Update Card Position", array("data" => $data, "error" => $e->getMessage()));
            
            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }
        return $response->withJSON(array('status' => 'error'));
    }

    public function moveCard(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $stack = array_key_exists("stack", $data) && !empty($data["stack"]) ? filter_var($data['stack'], FILTER_SANITIZE_NUMBER_INT) : null;
        $card = array_key_exists("card", $data) && !empty($data["card"]) ? filter_var($data['card'], FILTER_SANITIZE_NUMBER_INT) : null;

        try {
            $user = $this->ci->get('helper')->getUser()->id;
            $user_cards = $this->board_mapper->getUserCards($user);
            $user_stacks = $this->board_mapper->getUserStacks($user);

            if (!is_null($stack) && !is_null($card) && in_array($stack, $user_stacks) && in_array($card, $user_cards)) {
                $this->mapper->moveCard($card, $stack, $user);
                return $response->withJSON(array('status' => 'success'));
            }
        } catch (\Exception $e) {
            $logger = $this->ci->get('logger');
            $logger->addError("Move Card", array("data" => $data, "error" => $e->getMessage()));
            
            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }
        return $response->withJSON(array('status' => 'error'));
    }

    public function archive(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $id = $request->getAttribute('id');
        try {
            $this->preSave($id, $data);

            if (array_key_exists("archive", $data) && in_array($data["archive"], array(0, 1))) {
                $user = $this->ci->get('helper')->getUser()->id;
                $is_archived = $this->mapper->setArchive($id, $data["archive"], $user);
                $newResponse = $response->withJson(['is_archived' => $is_archived]);
                return $newResponse;
            } else {
                return $response->withJSON(array('status' => 'error', "error" => "missing data"));
            }
            $newResponse = $response->withJson(['is_archived' => $is_archived]);
            return $newResponse;
        } catch (\Exception $e) {
            $logger = $this->ci->get('logger');
            $logger->addError("Archive Card", array("data" => $data, "id" => $id, "error" => $e->getMessage()));
            
            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }
    }

    public function reminder() {

        $due_cards = $this->mapper->getCardReminder();
        $users = $this->user_mapper->getAll();
        $stacks = $this->stack_mapper->getAll();

        $subject = $this->ci->get('helper')->getTranslatedString('MAIL_CARD_REMINDER');

        $langugage = $this->ci->get('settings')['app']['i18n']['php'];
        $dateFormatPHP = $this->ci->get('settings')['app']['i18n']['dateformatPHP'];
        
        $fmt = new \IntlDateFormatter($langugage, NULL, NULL);
        $fmt->setPattern($dateFormatPHP['month_name_full']);


        foreach ($due_cards as $user_id => $cards) {
            $user = $users[$user_id];

            if ($user->mail && $user->mails_board_reminder == 1) {
                $variables = array(
                    'header' => '',
                    'subject' => $subject,
                    'headline' => sprintf($this->ci->get('helper')->getTranslatedString('HELLO') . ' %s', $user->name),
                    'content' => $this->ci->get('helper')->getTranslatedString('MAIL_CARD_REMINDER_DETAIL')
                );

                $user_cards = $due_cards[$user_id];

                $mail_content = '';

                foreach ($user_cards as $today => $stacks) {

                    if ($today == 1) {
                        $mail_content .= '<h2>' . $this->ci->get('helper')->getTranslatedString('TODAY') . ':</h2>';
                    } else {
                        $mail_content .= '<h2>' . $this->ci->get('helper')->getTranslatedString('OVERDUE') . ':</h2>';
                    }
                    foreach ($stacks as $board_name => $board) {
                        $url = $this->ci->get('helper')->getPath() . $this->ci->get('router')->pathFor('boards_view', array('hash' => $board["hash"]));
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
                    $this->ci->get('helper')->send_mail('mail/general.twig', $user->mail, $subject, $variables);
                }
            }
        }

        return true;
    }

}
