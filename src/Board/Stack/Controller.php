<?php

namespace App\Board\Stack;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;

class Controller extends \App\Base\Controller {

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            StackService $service) {
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

    public function updatePosition(Request $request, Response $response) {
        $data = $request->getParsedBody();

        try {

            if (array_key_exists("stack", $data) && !empty($data["stack"])) {
                $stacks = filter_var_array($data["stack"], FILTER_SANITIZE_NUMBER_INT);
                $this->service->move($stacks);

                $response_data = ['status' => 'success'];
                return $response->withJSON($response_data);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Update Stack Position", array("data" => $data, "error" => $e->getMessage()));

            $response_data = ['status' => 'error', "error" => $e->getMessage()];
            return $response->withJSON($response_data);
        }
        $response_data = ['status' => 'error'];
        return $response->withJSON($response_data);
    }

    public function archive(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $id = $request->getAttribute('id');
        try {
            $data1 = [];
            if (!$this->service->hasAccess($id, $data)) {
                throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
            }

            if (array_key_exists("archive", $data) && in_array($data["archive"], array(0, 1))) {

                $is_archived = $this->service->archive($id, $data["archive"]);

                $response_data = ['is_archived' => $is_archived];
                return $response->withJson($response_data);
            } else {
                $response_data = ['status' => 'error', "error" => "missing data"];
                return $response->withJSON($response_data);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Archive Stack", array("data" => $data, "id" => $id, "error" => $e->getMessage()));

            $response_data = ['status' => 'error', "error" => $e->getMessage()];
            return $response->withJSON($response_data);
        }
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
