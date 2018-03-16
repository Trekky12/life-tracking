<?php

namespace App\Board;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class BoardController extends \App\Base\Controller {

    private $users_preSave = array();
    private $users_afterSave = array();

    public function init() {
        $this->model = '\App\Board\Board';
        $this->index_route = 'boards';
        $this->edit_template = 'boards/edit.twig';

        $this->mapper = new \App\Board\BoardMapper($this->ci);
        $this->user_mapper = new \App\User\Mapper($this->ci);
        $this->stack_mapper = new \App\Board\StackMapper($this->ci);
        $this->card_mapper = new \App\Board\CardMapper($this->ci);
        $this->label_mapper = new \App\Board\LabelMapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $boards = $this->mapper->getVisibleBoards('name');
        return $this->ci->view->render($response, 'boards/index.twig', ['boards' => $boards]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
            $board_user = $this->mapper->getUsers($entry_id);
            $entry->setUsers($board_user);
        }

        $users = $this->user_mapper->getAll('name');

        $this->preEdit($entry_id);

        return $this->ci->view->render($response, 'boards/edit.twig', ['entry' => $entry, "users" => $users]);
    }

    public function view(Request $request, Response $response) {
        $hash = $request->getAttribute('hash');

        $board = $this->mapper->getBoardFromHash($hash);

        /**
         * Is the user allowed to view this board?
         */
        $board_user = $this->mapper->getUsers($board->id);
        $user = $this->ci->get('helper')->getUser()->id;
        if (!in_array($user, $board_user)) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
        }

        $show_archive = $this->ci->get('helper')->getSessionVar('show_archive', 0);


        /**
         * Get stacks with cards
         */
        $stacks = $this->stack_mapper->getStacksFromBoard($board->id, $show_archive);

        foreach ($stacks as &$stack) {
            $stack->cards = $this->card_mapper->getCardsFromStack($stack->id, $show_archive);
        }

        $users = $this->user_mapper->getAll('name');


        $card_user = $this->card_mapper->getCardsUser();

        $labels = $this->label_mapper->getLabelsFromBoard($board->id);

        $card_label = $this->label_mapper->getCardsLabel();


        return $this->ci->view->render($response, 'boards/view.twig', [
                    'board' => $board,
                    'stacks' => $stacks,
                    "users" => $users,
                    "card_user" => $card_user,
                    "labels" => $labels,
                    "card_label" => $card_label,
                    "show_archive" => $show_archive,
                    "board_user" => $board_user
        ]);
    }

    public function setArchive(Request $request, Response $response) {
        $data = $request->getParsedBody();

        if (array_key_exists("state", $data) && in_array($data["state"], array(0, 1))) {
            $this->ci->get('helper')->setSessionVar('show_archive', $data["state"]);
        }

        return $response->withJSON(array('status' => 'success'));
    }

    /**
     * save users 
     */
    protected function preSave($id, $data) {
        $this->users_preSave = $this->mapper->getUsers($id);
    }

    /**
     * notify user
     */
    protected function afterSave($id, $data) {

        /**
         * Notify new users
         */
        $my_user_id = intval($this->ci->get('helper')->getUser()->id);
        $this->users_afterSave = $this->mapper->getUsers($id);
        $new_users = array_diff($this->users_afterSave, $this->users_preSave);
        
        $board = $this->mapper->get($id);

        $subject = $this->ci->get('helper')->getTranslatedString('MAIL_ADDED_TO_BOARD');

        foreach ($new_users as $nu) {

            // except self
            if ($nu !== $my_user_id) {
                $user = $this->user_mapper->get($nu);

                if ($user->mail && $user->board_notification_mails == 1) {

                    $variables = array(
                        'header' => '',
                        'subject' => $subject,
                        'headline' => sprintf($this->ci->get('helper')->getTranslatedString('HELLO') . ' %s', $user->name),
                        'content' => sprintf($this->ci->get('helper')->getTranslatedString('MAIL_ADDED_TO_BOARD_DETAIL'), $this->ci->get('helper')->getPath() . $this->ci->get('router')->pathFor('boards_view', array('hash' => $board->hash)), $board->name)
                    );

                    $this->ci->get('helper')->send_mail('mail/general.twig', $user->mail, $subject, $variables);
                }
            }
        }
    }

}
