<?php

namespace App\Board;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Hashids\Hashids;
use Dflydev\FigCookies\FigRequestCookies;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Board\Board';
    protected $index_route = 'boards';
    protected $edit_template = 'boards/edit.twig';
    
    private $stack_mapper;
    private $card_mapper;
    private $label_mapper;
    
    private $users_preSave = array();
    private $users_afterSave = array();

    public function init() {
        $this->mapper = new Mapper($this->ci);
        $this->stack_mapper = new Stack\Mapper($this->ci);
        $this->card_mapper = new Card\Mapper($this->ci);
        $this->label_mapper = new Label\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $boards = $this->mapper->getUserItems('name');
        return $this->ci->view->render($response, 'boards/index.twig', ['boards' => $boards]);
    }

    public function view(Request $request, Response $response) {
        $hash = $request->getAttribute('hash');

        $board = $this->mapper->getFromHash($hash);

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

        //$sidebar_mobilevisible = filter_input(INPUT_COOKIE, 'sidebar_mobilevisible', FILTER_SANITIZE_NUMBER_INT);
        //$sidebar_desktophidden = filter_input(INPUT_COOKIE, 'sidebar_desktophidden', FILTER_SANITIZE_NUMBER_INT);
        $sidebar_mobilevisible = FigRequestCookies::get($request, 'sidebar_mobilevisible');
        $sidebar_desktophidden = FigRequestCookies::get($request, 'sidebar_desktophidden');

        return $this->ci->view->render($response, 'boards/view.twig', [
                    'board' => $board,
                    'stacks' => $stacks,
                    "users" => $users,
                    "card_user" => $card_user,
                    "labels" => $labels,
                    "card_label" => $card_label,
                    "show_archive" => $show_archive,
                    "board_user" => $board_user,
                    "sidebar" => [
                        "mobilevisible" => $sidebar_mobilevisible->getValue(),
                        "desktophidden" => $sidebar_desktophidden->getValue(),
                    ]
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
    protected function preSave($id, &$data, Request $request) {
        $this->users_preSave = $this->mapper->getUsers($id);
        $this->allowOwnerOnly($id);
    }

    /**
     * notify user
     */
    protected function afterSave($id, $data, Request $request) {
        $board = $this->mapper->get($id);

        /**
         * save hash
         */
        if (empty($board->hash)) {
            $hashids = new Hashids('', 10);
            $hash = $hashids->encode($id);
            $this->mapper->setHash($id, $hash);
        }

        /**
         * Notify new users
         */
        $my_user_id = intval($this->ci->get('helper')->getUser()->id);
        $this->users_afterSave = $this->mapper->getUsers($id);
        $new_users = array_diff($this->users_afterSave, $this->users_preSave);

        $subject = $this->ci->get('helper')->getTranslatedString('MAIL_ADDED_TO_BOARD');

        foreach ($new_users as $nu) {

            // except self
            if ($nu !== $my_user_id) {
                $user = $this->user_mapper->get($nu);

                if ($user->mail && $user->mails_board == 1) {

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

    /**
     * Does the user have access to this dataset?
     */
    protected function preEdit($id, Request $request) {
        $this->allowOwnerOnly($id);
    }

    protected function preDelete($id, Request $request) {
        $this->allowOwnerOnly($id);
    }

}
