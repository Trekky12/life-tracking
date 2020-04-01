<?php

namespace App\Application\Action\User\MobileFavorites;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\User\MobileFavorites\MobileFavoritesAdminWriter;

class MobileFavoritesSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, MobileFavoritesAdminWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        $user_id = $request->getAttribute('user');
        $user = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);

        $entry = $this->service->save($id, $data, $user);
        return $this->responder->respond($entry->withRouteName('users_mobile_favorites_admin')->withRouteParams(["user" => $user_id]));
    }

}
