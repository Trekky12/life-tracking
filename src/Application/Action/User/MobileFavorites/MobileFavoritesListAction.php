<?php

namespace App\Application\Action\User\MobileFavorites;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\MobileFavorites\MobileFavoriteAdminService;
use App\Application\Responder\HTMLResponder;

class MobileFavoritesListAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, MobileFavoriteAdminService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $user_id = $request->getAttribute('user');
        $index = $this->service->index($user_id);
        return $this->responder->respond($index->withTemplate('profile/mobile_favorites/index.twig'));
    }

}
