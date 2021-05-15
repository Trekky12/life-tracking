<?php

namespace App\Application\Action\Recipes\Cookbook;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Recipes\Cookbook\CookbookService;
use App\Application\Responder\HTMLTemplateResponder;

class CookbookListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, CookbookService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('recipes/cookbooks/index.twig'));
    }

}
