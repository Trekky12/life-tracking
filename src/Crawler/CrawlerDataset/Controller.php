<?php

namespace App\Crawler\CrawlerDataset;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Crawler\CrawlerService;

class Controller extends \App\Base\Controller {

    private $crawler_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            CrawlerDatasetService $service,
            CrawlerService $crawler_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->crawler_service = $crawler_service;
    }

    public function record(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $crawler_hash = $request->getAttribute('crawler');
        $identifier = array_key_exists("identifier", $data) ? filter_var($data["identifier"], FILTER_SANITIZE_STRING) : null;

        $crawler = $this->crawler_service->getFromHash($crawler_hash);

        if (!$this->crawler_service->isOwner($crawler->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $saveDataset = $this->service->saveDataset($crawler, $identifier, $data);

        if (!$saveDataset) {
            $this->logger->addError("Record Crawler Dataset " . $this->service->getDataObject(), array("error" => $e->getMessage()));

            $response_data = ['status' => 'error', "error" => $e->getMessage()];
            return $response->withJSON($response_data);
        }

        $response_data = ['status' => 'success'];
        return $response->withJSON($response_data);
    }

}
