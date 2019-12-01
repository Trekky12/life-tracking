<?php

namespace App\User;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    protected $model = '\App\User\User';
    protected $index_route = 'users';

    public function init() {
        
    }

    public function index(Request $request, Response $response) {
        $list = $this->user_mapper->getAll();
        return $this->ci->view->render($response, 'user/index.twig', ['list' => $list]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->user_mapper->get($entry_id);
        }

        return $this->ci->view->render($response, 'user/edit.twig', ['entry' => $entry, "roles" => $this->roles()]);
    }

    public function testMail(Request $request, Response $response) {

        $user_id = $request->getAttribute('user');
        $entry = $this->user_mapper->get($user_id);

        if ($entry->mail) {

            $subject = '[Life-Tracking] Test-Email';

            $variables = array(
                'header' => '',
                'subject' => $subject,
                'headline' => sprintf($this->ci->get('helper')->getTranslatedString('HELLO') . ' %s', $entry->name),
                'content' => $this->ci->get('helper')->getTranslatedString('THISISATESTEMAIL')
            );

            $return = $this->ci->get('helper')->send_mail('mail/general.twig', $entry->mail, $subject, $variables);

            if ($return) {
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("USER_EMAIL_SUCCESS"));
                $this->ci->get('flash')->addMessage('message_type', 'success');
            } else {
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("USER_EMAIL_ERROR"));
                $this->ci->get('flash')->addMessage('message_type', 'danger');
            }
        } else {
            $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("USER_HAS_NO_EMAIL"));
            $this->ci->get('flash')->addMessage('message_type', 'danger');
        }
        return $response->withRedirect($this->ci->get('router')->pathFor($this->index_route), 301);
    }

    private function roles() {
        return ['user', 'admin'];
    }

    protected function afterSave($id, $data, Request $request) {
        // notify new user
        // is new user?
        if (!array_key_exists("id", $data)) {
            $user = $this->user_mapper->get($id);
            if ($user->mail && $user->mails_user == 1) {

                $subject = sprintf($this->ci->get('helper')->getTranslatedString('MAIL_YOUR_USER_ACCOUNT_AT'), $this->ci->get('helper')->getPath());

                $variables = array(
                    'header' => '',
                    'subject' => $subject,
                    'headline' => sprintf($this->ci->get('helper')->getTranslatedString('HELLO') . ' %s', $user->name),
                    'content' => sprintf($this->ci->get('helper')->getTranslatedString('MAIL_USER_ACCOUNT_CREATED'), $this->ci->get('helper')->getPath(), $this->ci->get('helper')->getPath())
                    . '<br/>&nbsp;<br/>&nbsp;'
                    . sprintf($this->ci->get('helper')->getTranslatedString('MAIL_YOUR_USERNAME'), $user->login)
                );

                if (array_key_exists("set_password", $data)) {
                    $variables["content"] .= '<br/>&nbsp;' . sprintf($this->ci->get('helper')->getTranslatedString('MAIL_YOUR_PASSWORD'), $data["set_password"]);
                }

                if ($user->force_pw_change == 1) {
                    $variables["content"] .= '<br/>&nbsp;<br/>&nbsp;' . $this->ci->get('helper')->getTranslatedString('MAIL_FORCE_CHANGE_PASSWORD');
                }

                $this->ci->get('helper')->send_mail('mail/general.twig', $user->mail, $subject, $variables);
            }
        }
    }

}
