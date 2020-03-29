<?php

namespace App\Application\Action\Profile\MobileFavorites;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\MobileFavorites\MobileFavoriteService;
use App\Application\Responder\HTMLResponder;

class MobileFavoritesEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, MobileFavoriteService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($entry_id);
        return $this->responder->respond($data->withTemplate('profile/mobile_favorites/edit.twig'));
    }

}
