<?php

namespace App\Application\Action\Profile\MobileFavorites;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\User\MobileFavorites\MobileFavoritesWriter;

class MobileFavoritesSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, MobileFavoritesWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data);
        return $this->responder->respond($entry->withRouteName('users_mobile_favorites'));
    }

}
