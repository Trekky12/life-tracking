<?php

namespace App\Application\Error;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;

class MyErrorRenderer implements \Slim\Interfaces\ErrorRendererInterface {

    private $twig;
    private $logger;
    private $translation;

    public function __construct(Twig $twig, LoggerInterface $logger, Translator $translation) {
        $this->twig = $twig;
        $this->logger = $logger;
        $this->translation = $translation;
    }

    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string {
        if ($exception instanceof \Slim\Exception\HttpNotFoundException) {
            $this->logger->warning("Page not found");

            return $this->twig->fetch('error.twig', ['message' => $this->translation->getTranslatedString("NOTFOUND"), 'message_type' => 'danger']);
        } elseif ($exception instanceof \Slim\Exception\HttpNotAllowedException) {
            $this->logger->warning("Page not allowed");

            return $this->twig->fetch('error.twig', ['message' => $this->translation->getTranslatedString("NO_ACCESS"), 'message_type' => 'danger']);
        }

        if ($exception instanceof JSONException) {
            return json_encode(["status" => "error", "error" => $exception->getMessage()]);
        }

        $this->logger->critical($exception->getMessage());

        return $this->twig->fetch('error.twig', ['message' => $exception->getMessage(), 'message_type' => 'danger']);
    }

}
