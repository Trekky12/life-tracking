<?php

namespace App\Domain\Board;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Helper;
use App\Domain\Main\Utility\SessionUtility;
use App\Domain\User\UserService;
use App\Domain\Board\Stack\Mapper as StackMapper;
use App\Domain\Board\Card\Mapper as CardMapper;
use App\Domain\Board\Label\Mapper as LabelMapper;

class BoardService extends \App\Domain\Service {

    protected $dataobject = \App\Domain\Board\Board::class;
    protected $element_view_route = 'boards_edit';
    protected $module = "boards";
    private $helper;
    private $stack_mapper;
    private $card_mapper;
    private $label_mapper;
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
            StackMapper $stack_mapper,
            CardMapper $card_mapper,
            LabelMapper $label_mapper,
            UserService $user_service) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->helper = $helper;
        $this->mapper = $mapper;

        $this->stack_mapper = $stack_mapper;
        $this->card_mapper = $card_mapper;
        $this->label_mapper = $label_mapper;
        $this->user_service = $user_service;
    }

    public function getAllOrderedByName() {
        return $this->mapper->getUserItems('name');
    }

    public function view($board) {

        $board_users = $this->getUsers($board->id);

        $show_archive = SessionUtility::getSessionVar('show_archive', 0);

        /**
         * Get stacks with cards
         */
        $stacks = $this->stack_mapper->getStacksFromBoard($board->id, $show_archive);

        foreach ($stacks as &$stack) {
            $stack->cards = $this->card_mapper->getCardsFromStack($stack->id, $show_archive);
        }

        $users = $this->user_service->getAllUsersOrderedByLogin();

        $card_user = $this->card_mapper->getCardsUser();

        $labels = $this->label_mapper->getLabelsFromBoard($board->id);

        $card_label = $this->label_mapper->getCardsLabel();

        return [
            "board" => $board,
            "stacks" => $stacks,
            "users" => $users,
            "card_user" => $card_user,
            "labels" => $labels,
            "card_label" => $card_label,
            "show_archive" => $show_archive,
            "board_user" => $board_users,
        ];
    }

    public function setArchive($data) {
        if (array_key_exists("state", $data) && in_array($data["state"], array(0, 1))) {
            SessionUtility::setSessionVar('show_archive', $data["state"]);
        }
    }

    public function getBoardsOfUser($user) {
        return $this->mapper->getElementsOfUser($user);
    }

    public function saveUsersBefore($users) {
        $this->users_preSave = $users;
    }

    public function notifyUsers($id) {
        $board = $this->mapper->get($id);
        /**
         * Notify new users
         */
        $my_user_id = intval($this->current_user->getUser()->id);
        $users_afterSave = $this->getUsers($id);
        $new_users = array_diff($users_afterSave, $this->users_preSave);

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

}
