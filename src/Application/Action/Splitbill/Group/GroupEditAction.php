<?php

namespace App\Application\Action\Splitbill\Group;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Splitbill\Group\SplitbillGroupService;
use App\Application\Responder\HTMLTemplateResponder;

class GroupEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, SplitbillGroupService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($entry_id);
        return $this->responder->respond($data->withTemplate('splitbills/groups/edit.twig'));
    }

}
