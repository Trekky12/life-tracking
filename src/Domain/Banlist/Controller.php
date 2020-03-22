<?php

namespace App\Domain\Banlist;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Domain\Main\Translator;

class Controller {

    protected $logger;
    protected $twig;
    protected $flash;
    protected $translation;
    private $service;

    public function __construct(LoggerInterface $logger, Twig $twig, Flash $flash, Translator $translation, BanlistService $service) {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->flash = $flash;
        $this->translation = $translation;
        $this->service = $service;
    }

    public function index(Request $request, Response $response) {
        $list = $this->service->getBlockedIPAdresses();
        return $this->twig->render($response, 'main/banlist.twig', ["list" => $list]);
    }

    public function deleteIP(Request $request, Response $response) {
        $ip = $request->getAttribute('ip');

        $response_data = ['is_deleted' => false, 'error' => ''];

        try {

            $is_deleted = $this->service->deleteFailedLoginAttempts($ip);

            $response_data['is_deleted'] = $is_deleted;
            if ($is_deleted) {
                $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_SUCCESS_DELETE"));
                $this->flash->addMessage('message_type', 'success');

                $this->logger->addNotice("Delete successfully " . $this->dataobject, array("id" => $id));
            } else {
                $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_ERROR_DELETE"));
                $this->flash->addMessage('message_type', 'danger');

                $this->logger->addError("Delete failed " . $this->dataobject, array("id" => $id));
            }
        } catch (\Exception $e) {
            $response_data['error'] = $e->getMessage();
            $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_ERROR_DELETE"));
            $this->flash->addMessage('message_type', 'danger');

            $this->logger->addError("Delete failed " . $this->dataobject, array("id" => $id, "error" => $e->getMessage()));
        }

        return $response->withJson($response_data);
    }

}
