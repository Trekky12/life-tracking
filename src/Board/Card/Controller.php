<?php

namespace App\Board\Card;

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
            CardService $service) {
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
            // append card labels and usernames to output
            $rentry = $this->service->prepareCard($entry_id, $entry);
        } catch (\Exception $e) {
            $this->logger->addError("Get API " . $this->service->getDataObject(), array("id" => $entry_id, "error" => $e->getMessage()));

            $response_data = array('status' => 'error', "error" => $e->getMessage());
            return $response->withJSON($response_data);
        }

        $response_data = ['entry' => $rentry];
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

            // remove old labels
            $this->service->deleteLabelsFromCard($new_id);
            if (array_key_exists("labels", $data) && is_array($data["labels"])) {
                $labels = filter_var_array($data["labels"], FILTER_SANITIZE_NUMBER_INT);
                // save new labels
                $this->service->addLabelsToCard($new_id, $labels);
            }

            /**
             * Notify changed users
             */
            $this->service->notifyUsers($new_id);
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
            if (array_key_exists("card", $data) && !empty($data["card"])) {

                $cards = filter_var_array($data["card"], FILTER_SANITIZE_NUMBER_INT);
                $this->service->changePosition($cards);

                $response_data = ['status' => 'success'];
                return $response->withJSON($response_data);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Update Card Position", array("data" => $data, "error" => $e->getMessage()));

            $response_data = ['status' => 'error', "error" => $e->getMessage()];
            return $response->withJSON($response_data);
        }

        $response_data = ['status' => 'error'];
        return $response->withJSON($response_data);
    }

    public function moveCard(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $stack = array_key_exists("stack", $data) && !empty($data["stack"]) ? filter_var($data['stack'], FILTER_SANITIZE_NUMBER_INT) : null;
        $card = array_key_exists("card", $data) && !empty($data["card"]) ? filter_var($data['card'], FILTER_SANITIZE_NUMBER_INT) : null;

        try {
            if ($this->service->moveCard($stack, $card)) {
                $response_data = ['status' => 'success'];
                return $response->withJSON($response_data);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Move Card", array("data" => $data, "error" => $e->getMessage()));

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
            $response_data = ['is_archived' => $is_archived];
            return $response->withJson($response_data);
        } catch (\Exception $e) {
            $this->logger->addError("Archive Card", array("data" => $data, "id" => $id, "error" => $e->getMessage()));

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
