<?php

namespace App\Application\Action\Timesheets\Sheet;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\Sheet\SheetRemover;
use App\Application\Responder\DeleteResponder;

class SheetDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, SheetRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute("project");
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        $is_deletefollowing = array_key_exists('deletefollowing', $data) && $data['deletefollowing']!== '' ? intval(filter_var($data['deletefollowing'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $payload = $this->service->delete($id, ["project" => $project_hash, "is_deletefollowing" => $is_deletefollowing]);
        
        return $this->responder->respond($payload);
    }

}
