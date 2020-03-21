<?php

namespace App\User;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;

class Controller extends \App\Base\Controller {

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            UserService $service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
    }

    public function index(Request $request, Response $response) {
        $list = $this->service->getAllUsersOrderedByLogin();
        return $this->twig->render($response, 'user/index.twig', ['list' => $list]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = $this->service->getEntry($entry_id);

        $roles = $this->service->getRoles();

        return $this->twig->render($response, 'user/edit.twig', ['entry' => $entry, "roles" => $roles]);
    }
    
    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        $new_id = $this->doSave($id, $data, null);

        // notify new user
        // is new user?
        if (!array_key_exists("id", $data)) {
            $this->service->sendNewUserNotificationMail($new_id, $data);
        }
        
        $redirect_url = $this->router->urlFor('users');
        return $response->withRedirect($redirect_url, 301);
    }

    public function testMail(Request $request, Response $response) {

        $user_id = $request->getAttribute('user');

        list($has_mail, $result) = $this->service->sendTestNoficiationMail($user_id);

        if ($has_mail) {
            if ($result) {
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

        return $response->withRedirect($this->router->urlFor('users'), 301);
    }
    
    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $response_data = $this->doDelete($id);
        return $response->withJson($response_data);
    }

}
