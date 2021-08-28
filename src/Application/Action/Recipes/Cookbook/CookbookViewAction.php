<?php

namespace App\Application\Action\Recipes\Cookbook;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Recipes\Cookbook\CookbookService;
use App\Application\Responder\HTMLTemplateResponder;

class CookbookViewAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, CookbookService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('cookbook');
        
        $index = $this->service->view($hash);
        return $this->responder->respond($index->withTemplate('recipes/cookbooks/view.twig'));
    }

}
