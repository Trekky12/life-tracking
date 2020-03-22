<?php

namespace App\Domain\User;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Helper;

class UserService extends \App\Domain\Service {

    protected $dataobject = \App\Domain\User\User::class;
    protected $element_view_route = 'users_edit';
    private $helper;

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper,
            Helper $helper) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
        $this->helper = $helper;
    }

    public function getAllUsersOrderedByLogin() {
        return $this->mapper->getAll('login');
    }

    public function getAll() {
        return $this->mapper->getAll();
    }

    public function getUserFromLogin($username) {
        return $this->mapper->getUserFromLogin($username);
    }

    public function updatePassword($password) {
        $user = $this->current_user->getUser();
        $new_password_hash = password_hash($password, PASSWORD_DEFAULT);
        $this->mapper->update_password($user->id, $new_password_hash);
    }

    public function updateUser($data) {
        $user = $this->current_user->getUser();

        $new_user = new \App\Domain\User\User($data);
        $elements_changed = $this->mapper->update_profile($user->id, $new_user);

        return $elements_changed > 0;
    }

    public function comparePasswords($old_password, $new_password1, $new_password2) {
        if (empty($old_password) || empty($new_password1) || empty($new_password2) || $new_password1 !== $new_password2) {
            return false;
        }
        return true;
    }

    public function verifyPassword($password) {
        $user = $this->current_user->getUser();
        if (password_verify($password, $user->password)) {
            return true;
        }
        return false;
    }

    public function updateImage($user_id, $image) {
        return $this->mapper->update_image($user_id, $image);
    }

    public function getRoles() {
        return ['user', 'admin'];
    }

    public function sendTestNoficiationMail($id) {
        $entry = $this->mapper->get($id);

        if ($entry->mail) {

            $subject = '[Life-Tracking] Test-Email';

            $variables = array(
                'header' => '',
                'subject' => $subject,
                'headline' => sprintf($this->translation->getTranslatedString('HELLO') . ' %s', $entry->name),
                'content' => $this->translation->getTranslatedString('THISISATESTEMAIL')
            );

            $return = $this->helper->send_mail('mail/general.twig', $entry->mail, $subject, $variables);

            return array(true, $return);
        }
        return array(false, false);
    }

    public function sendNewUserNotificationMail($id, $data) {
        $user = $this->mapper->get($id);
        if ($user->mail && $user->mails_user == 1) {

            $subject = sprintf($this->translation->getTranslatedString('MAIL_YOUR_USER_ACCOUNT_AT'), $this->helper->getBaseURL());

            $variables = array(
                'header' => '',
                'subject' => $subject,
                'headline' => sprintf($this->translation->getTranslatedString('HELLO') . ' %s', $user->name),
                'content' => sprintf($this->translation->getTranslatedString('MAIL_USER_ACCOUNT_CREATED'), $this->helper->getBaseURL(), $this->helper->getBaseURL())
                . '<br/>&nbsp;<br/>&nbsp;'
                . sprintf($this->translation->getTranslatedString('MAIL_YOUR_USERNAME'), $user->login)
            );

            if (array_key_exists("password", $data)) {
                $variables["content"] .= '<br/>&nbsp;' . sprintf($this->translation->getTranslatedString('MAIL_YOUR_PASSWORD'), $data["password"]);
            }

            if ($user->force_pw_change == 1) {
                $variables["content"] .= '<br/>&nbsp;<br/>&nbsp;' . $this->translation->getTranslatedString('MAIL_FORCE_CHANGE_PASSWORD');
            }

            $this->helper->send_mail('mail/general.twig', $user->mail, $subject, $variables);
        }
    }

}
