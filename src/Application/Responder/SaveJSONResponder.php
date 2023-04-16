<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;

class SaveJSONResponder extends JSONResponder {

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation) {
        parent::__construct($responseFactory, $translation);
    }

    public function respond(Payload $payload): ResponseInterface {
        $response = parent::respond($payload);

        $data = ["status" => "success"];

        $result = $payload->getResult();
        if (is_object($result) && isset($result->id)) {
            $data["id"] = $result->id;
        } elseif (is_array($result) && array_key_exists("id", $result)) {
            $data["id"] = $result["id"];
        }

        $entry = $payload->getEntry();
        if ($entry) {
            $data["entry"] = $entry;
        }

        switch ($payload->getStatus()) {
            case Payload::$STATUS_PARSING_ERRORS:
                $data = ["status" => "error", "error" => $this->translation->getTranslatedString($result->getParsingErrors()[0])];
                break;
            case Payload::$STATUS_SAVE_ERROR:
                $data = ["status" => "error", "error" => $this->translation->getTranslatedString("ENTRY_ERROR_SAVE")];
                break;
            case Payload::$STATUS_ERROR:
                $data = ["status" => "error", "error" => $result];
                break;
        }

        $json = json_encode($data);
        $response->getBody()->write($json);

        return $response;
    }
}
