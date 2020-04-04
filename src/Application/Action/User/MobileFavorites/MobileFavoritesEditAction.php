<?php

namespace App\Application\Action\User\MobileFavorites;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\MobileFavorites\MobileFavoriteAdminService;
use App\Application\Responder\HTMLTemplateResponder;

class MobileFavoritesEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, MobileFavoriteAdminService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $user_id = $request->getAttribute('user');
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($user_id, $entry_id);
        return $this->responder->respond($data->withTemplate('profile/mobile_favorites/edit.twig'));
    }

}
