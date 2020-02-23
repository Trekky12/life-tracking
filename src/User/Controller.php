<?php

namespace App\User;

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;

class Controller extends \App\Base\Controller {

    protected $model = '\App\User\User';
    protected $element_view_route = 'users_edit';
    protected $index_route = 'users';

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        $this->mapper = $this->user_mapper;
    }

    public function index(Request $request, Response $response) {
        $list = $this->user_mapper->getAll('login');
        return $this->twig->render($response, 'user/index.twig', ['list' => $list]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->user_mapper->get($entry_id);
        }

        return $this->twig->render($response, 'user/edit.twig', ['entry' => $entry, "roles" => $this->roles()]);
    }

    public function testMail(Request $request, Response $response) {

        $user_id = $request->getAttribute('user');
        $entry = $this->user_mapper->get($user_id);

        if ($entry->mail) {

            $subject = '[Life-Tracking] Test-Email';

            $variables = array(
                'header' => '',
                'subject' => $subject,
                'headline' => sprintf($this->translation->getTranslatedString('HELLO') . ' %s', $entry->name),
                'content' => $this->translation->getTranslatedString('THISISATESTEMAIL')
            );

            $return = $this->helper->send_mail('mail/general.twig', $entry->mail, $subject, $variables);

            if ($return) {
                $this->flash->addMessage('message', $this->translation->getTranslatedString("USER_EMAIL_SUCCESS"));
                $this->flash->addMessage('message_type', 'success');
            } else {
                $this->flash->addMessage('message', $this->translation->getTranslatedString("USER_EMAIL_ERROR"));
                $this->flash->addMessage('message_type', 'danger');
            }
        } else {
            $this->flash->addMessage('message', $this->translation->getTranslatedString("USER_HAS_NO_EMAIL"));
            $this->flash->addMessage('message_type', 'danger');
        }
        return $response->withRedirect($this->router->pathFor($this->index_route), 301);
    }

    private function roles() {
        return ['user', 'admin'];
    }

    protected function afterSave($id, array $data, Request $request) {
        // notify new user
        // is new user?
        if (!array_key_exists("id", $data)) {
            $user = $this->user_mapper->get($id);
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

                if (array_key_exists("set_password", $data)) {
                    $variables["content"] .= '<br/>&nbsp;' . sprintf($this->translation->getTranslatedString('MAIL_YOUR_PASSWORD'), $data["set_password"]);
                }

                if ($user->force_pw_change == 1) {
                    $variables["content"] .= '<br/>&nbsp;<br/>&nbsp;' . $this->translation->getTranslatedString('MAIL_FORCE_CHANGE_PASSWORD');
                }

                $this->helper->send_mail('mail/general.twig', $user->mail, $subject, $variables);
            }
        }
    }

}
