<?php

namespace App\Domain\MailNotifications;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Helper;
use App\Application\Payload\Payload;
use App\Domain\Splitbill\Group\SplitbillGroupService;
use App\Domain\Board\BoardService;

class MailNotificationsService extends Service {

    private $cat_service;
    private $helper;
    private $splitbill_group_service;
    private $boards_service;
    private $mail_users_mapper;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            MailNotificationUsersMapper $users_mapper,
            MailNotificationCategoryMapper $cat_service,
            Helper $helper,
            SplitbillGroupService $splitbill_group_service,
            BoardService $boards_service) {
        parent::__construct($logger, $user);

        $this->mail_users_mapper = $users_mapper;
        $this->cat_service = $cat_service;

        $this->helper = $helper;

        $this->splitbill_group_service = $splitbill_group_service;
        $this->boards_service = $boards_service;

    }

    public function manage() {
        $categories = $this->cat_service->getAll();

        $user = $this->current_user->getUser();
        $user_categories = $this->mail_users_mapper->getCategoriesByUser($user->id);

        $splitbill_user_groups = $this->splitbill_group_service->getUserElements();
        $splitbill_all_groups = $this->splitbill_group_service->getAll();

        $boards_user_boards = $this->boards_service->getUserElements();
        $boards_all_boards = $this->boards_service->getAll();

        return new Payload(Payload::$RESULT_HTML, [
            "categories" => $categories,
            "user_categories" => $user_categories,
            "splitbill" => [
                "groups" => $splitbill_all_groups,
                "user_groups" => $splitbill_user_groups
            ],
            "boards" => [
                "boards" => $boards_all_boards,
                "user_boards" => $boards_user_boards
            ],
        ]);
    }

    public function setCategoryForUser($data) {

        $cat = array_key_exists('category', $data) ? filter_var($data['category'], FILTER_SANITIZE_STRING) : "";
        $type = array_key_exists('type', $data) ? intval(filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $category = intval($cat);
        $object_id = null;
        if (strpos($cat, "_")) {
            $cat_and_id = explode("_", $cat);
            $category = intval($cat_and_id[0]);
            $object_id = intval($cat_and_id[1]);
        }

        $user = $this->current_user->getUser();

        if ($type == 1) {
            $this->mail_users_mapper->addCategory($user->id, $category, $object_id);
        } else {
            $this->mail_users_mapper->deleteCategory($user->id, $category, $object_id);
        }
        $result = ["status" => "success"];
        return new Payload(Payload::$RESULT_JSON, $result);
    }
    
    public function sendMailToUserWithCategory($user, $identifier, $template, $subject, $body, $object_id = null) {
        try {
            $category = $this->cat_service->getCategoryByIdentifier($identifier);

            $user_has_category = $this->mail_users_mapper->doesUserHaveCategory($category->id, $user->id, $object_id);
            if ($user_has_category) {
                $this->helper->send_mail($template, $user->mail, $subject, $body);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error with Mail", array("error" => $e->getMessage(), "code" => $e->getCode()));
        }
    }

    
}
