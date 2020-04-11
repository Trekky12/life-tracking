<?php

namespace App\Domain\Splitbill\Bill;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Helper;
use App\Domain\Notifications\NotificationsService;
use App\Domain\User\UserService;

class BillNotificationService {

    private $logger;
    private $current_user;
    private $mapper;
    private $router;
    private $helper;
    private $translation;
    private $user_service;
    private $notification_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, BillMapper $mapper, RouteParser $router, Helper $helper, Translator $translation, UserService $user_service, NotificationsService $notification_service) {
        $this->logger = $logger;
        $this->current_user = $user;
        $this->mapper = $mapper;
        $this->router = $router;
        $this->helper = $helper;
        $this->translation = $translation;

        $this->user_service = $user_service;
        $this->notification_service = $notification_service;
    }

    public function notifyUsers($type, $bill, $sbgroup, $existing_balance) {
        /**
         * Notify users
         */
        $users = $this->user_service->getAll();

        $me = $this->current_user->getUser();
        $my_user_id = intval($me->id);
        $users_afterSave = $this->mapper->getBillUsers($bill->id);

        $new_balances = $this->mapper->getBalance($bill->id);
        $billValue = $this->mapper->getBillSpend($bill->id);

        $group_path = $this->router->urlFor('splitbill_bills', array('group' => $sbgroup->getHash()));
        $group_url = $this->helper->getBaseURL() . $group_path;

        $is_new_bill = count($existing_balance) == 0;

        if ($bill->settleup === 0) {

            if ($type == "edit") {
                $subject1 = $this->translation->getTranslatedString('MAIL_SPLITTED_BILL_ADDED_SUBJECT');
                $content1 = $this->translation->getTranslatedString('MAIL_SPLITTED_BILL_ADDED_DETAIL');
                if (!$is_new_bill) {
                    $subject1 = $this->translation->getTranslatedString('MAIL_SPLITTED_BILL_UPDATE_SUBJECT');
                    $content1 = $this->translation->getTranslatedString('MAIL_SPLITTED_BILL_UPDATE_DETAIL');
                }
            } else {
                $subject1 = $this->translation->getTranslatedString('MAIL_SPLITTED_BILL_DELETED_SUBJECT');
                $content1 = $this->translation->getTranslatedString('MAIL_SPLITTED_BILL_DELETED_DETAIL');
            }

            $subject = sprintf($subject1, $bill->name);
            $content = sprintf($content1, $me->name, $bill->name, $billValue, $sbgroup->currency, $group_url, $sbgroup->name);
            $lang_spend = $this->translation->getTranslatedString('SPEND');
            $lang_paid = $this->translation->getTranslatedString('PAID');
        } else {
            if ($type == "edit") {
                $subject1 = $this->translation->getTranslatedString('MAIL_SPLITTED_BILL_SETTLEUP_SUBJECT');
                $content1 = $this->translation->getTranslatedString('MAIL_SPLITTED_BILL_SETTLEUP_DETAIL');
                if (!$is_new_bill) {
                    $subject1 = $this->translation->getTranslatedString('MAIL_SPLITTED_BILL_SETTLEUP_UPDATE_SUBJECT');
                    $content1 = $this->translation->getTranslatedString('MAIL_SPLITTED_BILL_SETTLEUP_UPDATE_DETAIL');
                }
            } else {
                $subject1 = $this->translation->getTranslatedString('MAIL_SPLITTED_BILL_SETTLEUP_DELETED_SUBJECT');
                $content1 = $this->translation->getTranslatedString('MAIL_SPLITTED_BILL_SETTLEUP_DELETED_DETAIL');
            }

            $subject = sprintf($subject1, $me->name);
            $content = sprintf($content1, $me->name, $billValue, $sbgroup->currency, $group_url, $sbgroup->name);
            $lang_spend = $this->translation->getTranslatedString('SPLITBILLS_SETTLE_UP_SENDER');
            $lang_paid = $this->translation->getTranslatedString('SPLITBILLS_SETTLE_UP_RECEIVER');
        }

        foreach ($users_afterSave as $nu) {

            // except self
            if ($nu !== $my_user_id) {
                $user = $users[$nu];

                // Mail
                if ($user->mail && $user->mails_splitted_bills == 1) {

                    $variables = array(
                        'header' => '',
                        'subject' => $subject,
                        'headline' => sprintf($this->translation->getTranslatedString('HELLO') . ' %s', $user->name),
                        'content' => $content,
                        'currency' => $sbgroup->currency,
                        'balances' => $new_balances,
                        'users' => $users,
                        'LANG_SPEND' => $lang_spend,
                        'LANG_PAID' => $lang_paid,
                    );

                    $this->helper->send_mail('mail/splitted_bill.twig', $user->mail, $subject, $variables);
                }

                // Notification
                $this->notification_service->sendNotificationsToUserWithCategory($user->id, "NOTIFICATION_CATEGORY_SPLITTED_BILLS", $subject, $content, $group_path);
            }
        }
    }

}
