<?php

namespace App\Application\Action\Timesheets\Customer;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\Customer\CustomerService;
use App\Application\Responder\HTMLTemplateResponder;

class CustomerListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, CustomerService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');
        $archive = $request->getParam('archive', 0);
        $index = $this->service->index($project_hash, $archive);
        return $this->responder->respond($index->withTemplate('timesheets/customer/index.twig'));
    }

}
