<?php

namespace App\Application\Action\Profile\MobileFavorites;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\MobileFavorites\MobileFavoriteService;
use App\Application\Responder\HTMLTemplateResponder;

class MobileFavoritesListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, MobileFavoriteService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('profile/mobile_favorites/index.twig'));
    }

}
