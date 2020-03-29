<?php

namespace App\Application\Action\Profile;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\Profile\ProfileService;
use App\Application\Responder\Profile\ChangeProfileImageResponder;

class ProfileImageSaveAction {

    private $responder;
    private $service;

    public function __construct(ChangeProfileImageResponder $responder, ProfileService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        /**
         * Handle uploaded file
         * @link https://akrabat.com/psr-7-file-uploads-in-slim-3/
         */
        $files = $request->getUploadedFiles();
        $payload = $this->service->updateProfileImage($data, $files);

        return $this->responder->respond($payload);
    }

}
