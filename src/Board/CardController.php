<?php

namespace App\Board;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CardController extends \App\Base\Controller {

    private $card_users_preSave = array();
    private $card_users_afterSave = array();

    public function init() {
        $this->model = '\App\Board\Card';

        $this->mapper = new \App\Board\CardMapper($this->ci);
        $this->board_mapper = new \App\Board\BoardMapper($this->ci);
        $this->stack_mapper = new \App\Board\StackMapper($this->ci);
        $this->label_mapper = new \App\Board\LabelMapper($this->ci);
        $this->user_mapper = new \App\User\Mapper($this->ci);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, $data) {
        $user = $this->ci->get('helper')->getUser()->id;
        $this->card_users_preSave = array();

        if (!is_null($id)) {
            $user_cards = $this->board_mapper->getUserCards($user);
            if (!in_array($id, $user_cards)) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }

            /**
             * Get users pre change
             */
            $this->card_users_preSave = $this->mapper->getUsers($id);
        } elseif (is_array($data)) {
            $user_stacks = $this->board_mapper->getUserStacks($user);
            if (!array_key_exists("stack", $data) || !in_array($data["stack"], $user_stacks)) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

    /**
     * Save labels
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
            
        }

        /**
         * Notify changed users
         */
        $my_user_id = intval($this->ci->get('helper')->getUser()->id);
        $this->card_users_afterSave = $this->mapper->getUsers($id);
        $new_users = array_diff($this->card_users_afterSave, $this->card_users_preSave);

        $board = $this->board_mapper->get($board_id);
        $card = $this->mapper->get($id);
        
        $subject = $this->ci->get('helper')->getTranslatedString('MAIL_ADDED_TO_CARD');

        foreach ($new_users as $nu) {

            // except self
            if ($nu !== $my_user_id) {
                $user = $this->user_mapper->get($nu);

                if ($user->mail && $user->board_notification_mails == 1) {
                    
                    $variables = array(
                        'header' => '',
                        'subject' => $subject,
                        'headline' => sprintf($this->ci->get('helper')->getTranslatedString('HELLO') . ' %s', $user->name),
                        'content' => sprintf($this->ci->get('helper')->getTranslatedString('MAIL_ADDED_TO_CARD_DETAIL'), $this->ci->get('helper')->getPath() . $this->ci->get('router')->pathFor('boards_view', array('hash' => $board->hash)), $board->name, $card->title)
                        . '<br/>'
                    );

                    if ($card->description) {
                        $variables["content"] .= '<br/><strong>' . $this->ci->get('helper')->getTranslatedString('DESCRIPTION') . ':</strong> <br/>' . nl2br($card->description).'<br/>';
                    }
                    if ($card->date) {
                        $langugage = $this->ci->get('settings')['app']['i18n']['php'];
                        $fmt = new \IntlDateFormatter($langugage, NULL, NULL);
                        $fmt->setPattern('dd. MMMM y');

                        $dateObj = new \DateTime($card->date);
                        $variables["content"] .= '<br/><strong>' . $this->ci->get('helper')->getTranslatedString('DATE') . ':</strong> <br/>' . $fmt->format($dateObj) .'<br/>';
                    }
                    if ($card->time) {
                        $variables["content"] .= '<br/><strong>' . $this->ci->get('helper')->getTranslatedString('TIME') . ':</strong> <br/>' . $card->time.'<br/>';
                    }

                    $this->ci->get('helper')->send_mail('mail/general.twig', $user->mail, $subject, $variables);
                }
            }
        }
    }

    /**
     * append card labels to output
     * @param type $id
     * @param type $entry
     * @return type
     */
    protected function afterGetAPI($id, $entry) {
        $card_labels = $this->label_mapper->getLabelsFromCard($id);
        $entry->labels = $card_labels;
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
                        $this->mapper->updatePosition($item, $position,$user);
                    }
                }
                return $response->withJSON(array('status' => 'success'));
            }
        } catch (\Exception $e) {
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
            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }
        return $response->withJSON(array('status' => 'error'));
    }

    public function archive(Request $request, Response $response) {
        $data = $request->getParsedBody();
        try {
            $id = $request->getAttribute('id');

            $this->preSave($id, null);

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
            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }
    }

}
