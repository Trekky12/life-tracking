<?php

namespace App\Application\Error;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Admin\Banlist\BanlistService;
use App\Domain\Main\Utility\Utility;

class MyErrorRenderer implements \Slim\Interfaces\ErrorRendererInterface {

    private $twig;
    private $logger;
    private $translation;
    private $banlist_service;

    public function __construct(
        Twig $twig,
        LoggerInterface $logger,
        Translator $translation,
        BanlistService $banlist_service
    ) {
        $this->twig = $twig;
        $this->logger = $logger;
        $this->translation = $translation;
        $this->banlist_service = $banlist_service;
    }

    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string {

        if ($exception instanceof PageNotFoundException) {
            $this->logger->warning("Page not found from anonymous");

            if (!is_null(Utility::getIP())) {
                $this->banlist_service->addBan(Utility::getIP());
            }

            return $this->twig->fetch('error.twig', ['message' => $this->translation->getTranslatedString("NOTFOUND"), 'message_type' => 'danger']);
        } elseif ($exception instanceof \Slim\Exception\HttpNotFoundException) {
            $this->logger->warning("Page not found");

            return $this->twig->fetch('error.twig', ['message' => $this->translation->getTranslatedString("NOTFOUND"), 'message_type' => 'danger']);
        } elseif ($exception instanceof \Slim\Exception\HttpMethodNotAllowedException) {
            $this->logger->warning("Method not allowed");

            return $this->twig->fetch('error.twig', ['message' => $this->translation->getTranslatedString("NO_ACCESS"), 'message_type' => 'danger']);
        }

        if ($exception instanceof JSONException) {
            return json_encode(["status" => "error", "error" => $exception->getMessage()]);
        }

        if ($exception instanceof CSRFException) {
            $data = $exception->getData();
            return $this->twig->fetch('error.twig', [
                'message' => $this->translation->getTranslatedString("REQUEST_ERROR"),
                'message_type' => 'danger',
                'data' => $data
            ]);
        }

        $this->logger->critical($exception->getMessage());

        return $this->twig->fetch('error.twig', ['message' => $exception->getMessage(), 'message_type' => 'danger']);
    }
}
