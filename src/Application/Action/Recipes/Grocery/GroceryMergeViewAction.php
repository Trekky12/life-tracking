<?php

namespace App\Application\Action\Recipes\Grocery;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Recipes\Grocery\GroceryService;
use App\Application\Responder\HTMLTemplateResponder;

class GroceryMergeViewAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, GroceryService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $this->service->view_merge($entry_id);
        return $this->responder->respond($data->withTemplate('recipes/groceries/merge.twig'));
    }

}
