<?php

namespace App\User;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    private $car_mapper;

    public function init() {
        $this->model = '\App\User\User';
        $this->index_route = 'users';

        $this->mapper = new \App\User\Mapper($this->ci);
        $this->car_mapper = new \App\Car\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getAll();

        return $this->ci->view->render($response, 'user/index.twig', ['list' => $list]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        return $this->ci->view->render($response, 'user/edit.twig', ['entry' => $entry, "roles" => $this->roles()]);
    }

    public function changePassword(Request $request, Response $response) {

        $user = $this->ci->get('helper')->getUser();


        if ($request->isPost()) {

            $data = $request->getParsedBody();
            $old_password = array_key_exists('oldpassword', $data) ? filter_var($data['oldpassword'], FILTER_SANITIZE_STRING) : null;
            $new_password1 = array_key_exists('newpassword1', $data) ? filter_var($data['newpassword1'], FILTER_SANITIZE_STRING) : null;
            $new_password2 = array_key_exists('newpassword2', $data) ? filter_var($data['newpassword2'], FILTER_SANITIZE_STRING) : null;

            if (empty($old_password) || empty($new_password1) || empty($new_password2) || $new_password1 !== $new_password2) {
                return $this->ci->view->render($response, 'user/changepw.twig', array("user" => $user, "message" => $this->ci->get('helper')->getTranslatedString("PASSWORD1AND2MUSTMATCH"), "message_type" => "danger"));
            }


            /**
             * Verify old password
             */
            if (!password_verify($old_password, $user->password)) {

                return $this->ci->view->render($response, 'user/changepw.twig', array("user" => $user, "message" => $this->ci->get('helper')->getTranslatedString("PASSWORD_WRONG_OLD"), "message_type" => "danger"));
            }

            /**
             * Update Password
             */
            $new_password_hash = password_hash($new_password1, PASSWORD_DEFAULT);
            $this->mapper->update_password($user->id, $new_password_hash);

            $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("PASSWORD_CHANGE_SUCCESS"));
            $this->ci->get('flash')->addMessage('message_type', 'success');

            return $response->withRedirect($this->ci->get('router')->pathFor($this->index_route), 301);
        }
        return $this->ci->view->render($response, 'user/changepw.twig', ["user" => $user]);
    }

    public function testMail(Request $request, Response $response) {

        $user_id = $request->getAttribute('id');
        $entry = $this->mapper->get($user_id);

        if ($entry->mail) {

            $subject = '[Life-Tracking] Test-Email';

            $variables = array(
                'header' => '',
                'subject' => $subject,
                'headline' => sprintf($this->ci->get('helper')->getTranslatedString('HELLO') . ' %s', $entry->name),
                'content' => $this->ci->get('helper')->getTranslatedString('THISISATESTEMAIL')
            );

            $return = $this->ci->get('helper')->send_mail('mail/test.twig', $entry->mail, $subject, $variables);

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

}
