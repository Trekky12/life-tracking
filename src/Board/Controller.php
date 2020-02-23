<?php

namespace App\Board;

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;
use Hashids\Hashids;
use Dflydev\FigCookies\FigRequestCookies;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Board\Board';
    protected $index_route = 'boards';
    protected $edit_template = 'boards/edit.twig';
    protected $element_view_route = 'boards_edit';
    protected $module = "boards";
    private $stack_mapper;
    private $card_mapper;
    private $label_mapper;
    private $users_preSave = array();
    private $users_afterSave = array();

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        
        $this->mapper = new Mapper($this->db, $this->translation, $user);
        $this->stack_mapper = new Stack\Mapper($this->db, $this->translation, $user);
        $this->card_mapper = new Card\Mapper($this->db, $this->translation, $user);
        $this->label_mapper = new Label\Mapper($this->db, $this->translation, $user);
    }

    public function index(Request $request, Response $response) {
        $boards = $this->mapper->getUserItems('name');
        return $this->twig->render($response, 'boards/index.twig', ['boards' => $boards]);
    }

    public function view(Request $request, Response $response) {
        $hash = $request->getAttribute('hash');

        $board = $this->mapper->getFromHash($hash);

        /**
         * Is the user allowed to view this board?
         */
        $board_user = $this->mapper->getUsers($board->id);
        $user = $this->user_helper->getUser()->id;
        if (!in_array($user, $board_user)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $show_archive = $this->helper->getSessionVar('show_archive', 0);


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

        return $this->twig->render($response, 'boards/view.twig', [
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
            $this->helper->setSessionVar('show_archive', $data["state"]);
        }

        $response_data = ['status' => 'success'];
        return $response->withJSON($response_data);
    }

    /**
     * save users 
     */
    protected function preSave($id, array &$data, Request $request) {
        $this->users_preSave = $this->mapper->getUsers($id);
        $this->allowOwnerOnly($id);
    }

    /**
     * notify user
     */
    protected function afterSave($id, array $data, Request $request) {
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
        $my_user_id = intval($this->user_helper->getUser()->id);
        $this->users_afterSave = $this->mapper->getUsers($id);
        $new_users = array_diff($this->users_afterSave, $this->users_preSave);

        $subject = $this->translation->getTranslatedString('MAIL_ADDED_TO_BOARD');

        foreach ($new_users as $nu) {

            // except self
            if ($nu !== $my_user_id) {
                $user = $this->user_mapper->get($nu);

                if ($user->mail && $user->mails_board == 1) {

                    $variables = array(
                        'header' => '',
                        'subject' => $subject,
                        'headline' => sprintf($this->translation->getTranslatedString('HELLO') . ' %s', $user->name),
                        'content' => sprintf($this->translation->getTranslatedString('MAIL_ADDED_TO_BOARD_DETAIL'), $this->helper->getBaseURL() . $this->router->pathFor('boards_view', array('hash' => $board->getHash())), $board->name)
                    );

                    $this->helper->send_mail('mail/general.twig', $user->mail, $subject, $variables);
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
