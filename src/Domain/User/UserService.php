<?php

namespace App\Domain\User;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Helper;
use App\Domain\Main\Translator;
use App\Application\Payload\Payload;

class UserService extends Service {

    private $helper;
    private $translation;

    public function __construct(LoggerInterface $logger, CurrentUser $user, UserMapper $mapper, Helper $helper, Translator $translation) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->helper = $helper;
        $this->translation = $translation;
    }

    public function getAllUsersOrderedByLogin() {
        return $this->mapper->getAll('login');
    }

    public function getAll() {
        return $this->mapper->getAll();
    }

    public function getUsersData($users = []) {
        return $this->mapper->getUsersData($users);
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

    public function testMail($user_id) {
        list($has_mail, $result) = $this->sendTestNotificationMail($user_id);
        if ($has_mail) {
            if ($result) {
                return new Payload(Payload::$STATUS_MAIL_SUCCESS);
            } else {
                return new Payload(Payload::$STATUS_MAIL_ERROR);
            }
        }
        return new Payload(Payload::$STATUS_NO_MAIL);
    }

    private function sendTestNotificationMail($id) {

        $entry = $this->mapper->get($id);

        if ($entry->mail) {

            $subject = '[Life-Tracking] Test-Email';

            $variables = array(
                'header' => '',
                'subject' => $subject,
                'headline' => sprintf($this->translation->getTranslatedString('HELLO') . ' %s', $entry->name),
                'content' => $this->translation->getTranslatedString('THISISATESTEMAIL')
            );


            $this->logger->info("Send test mail");

            $return = $this->helper->send_mail('mail/general.twig', $entry->mail, $subject, $variables);

            return array(true, $return);
        }
        return array(false, false);
    }

    public function index() {
        $list = $this->getAllUsersOrderedByLogin();
        return new Payload(Payload::$RESULT_HTML, ['list' => $list]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        $roles = $this->getRoles();
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, "roles" => $roles]);
    }
    
    public function setTwoFactorAuthSecret($secret) {
        $user = $this->current_user->getUser();
        $this->mapper->update_secret($user->id, $secret);
    }
    
    public function getData($data) {
        
        $response_data = ["data" => [], "status" => "success"];
        $query = array_key_exists('query', $data) ? filter_var($data['query'], FILTER_SANITIZE_STRING) : "";
        $module = array_key_exists('module', $data) ? filter_var($data['module'], FILTER_SANITIZE_STRING) : null;
        $users = filter_var_array($data["users"], FILTER_SANITIZE_NUMBER_INT);
        
        $response_data["data"] = $this->mapper->getUsersWithModule($query, $module, $users);
        
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

}
