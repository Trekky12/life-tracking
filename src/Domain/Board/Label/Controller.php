<?php

namespace App\Domain\Board\Label;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;

class Controller extends \App\Domain\Base\Controller {

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            LabelService $service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
    }

    public function getAPI(Request $request, Response $response) {
        $entry_id = $request->getAttribute('id');

        try {
            if (!$this->service->hasAccess($entry_id, [])) {
                throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
            }

            $entry = $this->service->getEntry($entry_id);

            if ($entry->name) {
                $entry->name = htmlspecialchars_decode($entry->name);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Get API " . $this->service->getDataObject(), array("id" => $entry_id, "error" => $e->getMessage()));

            $response_data = array('status' => 'error', "error" => $e->getMessage());
            return $response->withJSON($response_data);
        }

        $response_data = ['entry' => $entry];
        return $response->withJson($response_data);
    }

    public function saveAPI(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        try {

            if (!$this->service->hasAccess($id, $data)) {
                throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
            }

            $new_id = $this->doSave($id, $data, null);
        } catch (\Exception $e) {
            $this->logger->addError("Save API " . $this->service->getDataObject(), array("error" => $e->getMessage()));

            $response_data = array('status' => 'error', "error" => $e->getMessage());
            return $response->withJSON($response_data);
        }

        $response_data = array('status' => 'success');
        return $response->withJSON($response_data);
    }

    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');

        if (!$this->service->hasAccess($id)) {
            $response_data = ['is_deleted' => false, 'error' => $this->translation->getTranslatedString('NO_ACCESS')];
        } else {
            $response_data = $this->doDelete($id);
        }
        return $response->withJson($response_data);
    }

}
