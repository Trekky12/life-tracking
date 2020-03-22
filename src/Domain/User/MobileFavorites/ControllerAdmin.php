<?php

namespace App\Domain\User\MobileFavorites;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;

class ControllerAdmin extends \App\Domain\Base\Controller {


    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            MobileFavoriteAdminService $service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
    }

    public function index(Request $request, Response $response) {

        $user_id = $request->getAttribute('user');

        $user = $this->service->setUserForMapper($user_id);

        $list = $this->service->getMobileFavorites();

        return $this->twig->render($response, 'profile/mobile_favorites/index.twig', ['list' => $list, 'for_user' => $user]);
    }

    public function edit(Request $request, Response $response) {

        // get user and change filter to this user
        $user_id = $request->getAttribute('user');

        $user = $this->service->setUserForMapper($user_id);

        // load entry
        $entry_id = $request->getAttribute('id');
        $entry = $this->service->getEntry($entry_id);

        return $this->twig->render($response, 'profile/mobile_favorites/edit.twig', ['entry' => $entry, 'for_user' => $user]);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        // get user from attribute
        $user_id = $request->getAttribute('user');
        $user = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);

        $new_id = $this->doSave($id, $data, $user);

        $redirect_url = $this->router->urlFor('users_mobile_favorites_admin', ["user" => $user_id]);
        return $response->withRedirect($redirect_url, 301);
    }

    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');

        // get user from attribute
        $user_id = $request->getAttribute('user');
        $user = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);

        $response_data = $this->doDelete($id, $user);

        return $response->withJson($response_data);
    }

}
