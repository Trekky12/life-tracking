<?php

namespace App\Banlist;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;

class Controller {

    public static $MAX_ATTEMPTS = 2;
    protected $logger;
    protected $twig;
    protected $flash;
    protected $translation;

    public function __construct(ContainerInterface $ci) {
        $this->logger = $ci->get('logger');
        $this->twig = $ci->get('view');
        $this->flash = $ci->get('flash');
        $this->translation = $ci->get('translation');

        $this->db = $ci->get('db');

        $this->mapper = new Mapper($this->db, $this->translation);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getBlockedIPAdresses(self::$MAX_ATTEMPTS);
        return $this->twig->render($response, 'main/banlist.twig', ["list" => $list]);
    }

    public function deleteIP(Request $request, Response $response) {
        $ip = $request->getAttribute('ip');

        $response_data = ['is_deleted' => false, 'error' => ''];

        try {
            $this->preDelete($id, $request);

            $is_deleted = $this->deleteFailedLoginAttempts($ip);
            $response_data['is_deleted'] = $is_deleted;
            if ($is_deleted) {
                $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_SUCCESS_DELETE"));
                $this->flash->addMessage('message_type', 'success');

                $this->logger->addNotice("Delete successfully " . $this->model, array("id" => $id));
            } else {
                $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_ERROR_DELETE"));
                $this->flash->addMessage('message_type', 'danger');

                $this->logger->addError("Delete failed " . $this->model, array("id" => $id));
            }
        } catch (\Exception $e) {
            $response_data['error'] = $e->getMessage();
            $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_ERROR_DELETE"));
            $this->flash->addMessage('message_type', 'danger');

            $this->logger->addError("Delete failed " . $this->model, array("id" => $id, "error" => $e->getMessage()));
        }

        $this->afterDelete($id, $request);

        return $response->withJson($response_data);
    }

    public function getFailedLoginAttempts($ip) {
        return $this->mapper->getFailedLoginAttempts($ip);
    }

    public function deleteFailedLoginAttempts($ip) {
        return $this->mapper->deleteFailedLoginAttempts($ip);
    }

    public function addBan($ip, $username) {
        $model = new \App\Base\Model(array('ip' => $ip, 'username' => $username));
        $this->mapper->insert($model);
    }

    public function isBlocked($ip) {
        return $this->getFailedLoginAttempts($ip) > self::$MAX_ATTEMPTS;
    }

}
