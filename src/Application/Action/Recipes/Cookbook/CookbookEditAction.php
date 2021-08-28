<?php

namespace App\Application\Action\Recipes\Cookbook;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Recipes\Cookbook\CookbookService;
use App\Application\Responder\HTMLTemplateResponder;

class CookbookEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, CookbookService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($entry_id);
        return $this->responder->respond($data->withTemplate('recipes/cookbooks/edit.twig'));
    }

}
