<?php

namespace App\Banlist;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    protected $ci;
    
    public static $MAX_ATTEMPTS = 2;

    public function init() {        
        $this->mapper = new Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getBlockedIPAdresses(self::$MAX_ATTEMPTS);
        return $this->ci->get('view')->render($response, 'main/banlist.twig', ["list" => $list]);
    }
    
    public function deleteIP(Request $request, Response $response){
        $ip = $request->getAttribute('ip');

        $data = ['is_deleted' => false, 'error' => ''];

        try {
            $this->preDelete($id, $request);

            $is_deleted = $this->deleteFailedLoginAttempts($ip);
            $data ['is_deleted'] = $is_deleted;
            if ($is_deleted) {
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_SUCCESS_DELETE"));
                $this->ci->get('flash')->addMessage('message_type', 'success');

                $this->logger->addNotice("Delete successfully " . $this->model, array("id" => $id));
            } else {
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_ERROR_DELETE"));
                $this->ci->get('flash')->addMessage('message_type', 'danger');

                $this->logger->addError("Delete failed " . $this->model, array("id" => $id));
            }
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
            $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_ERROR_DELETE"));
            $this->ci->get('flash')->addMessage('message_type', 'danger');

            $this->logger->addError("Delete failed " . $this->model, array("id" => $id, "error" => $e->getMessage()));
        }
        
        $this->afterDelete($id, $request);

        $newResponse = $response->withJson($data);

        return $newResponse;
    }
    
    public function getFailedLoginAttempts($ip){
        return $this->mapper->getFailedLoginAttempts($ip);
    }
    
    public function deleteFailedLoginAttempts($ip){
        return $this->mapper->deleteFailedLoginAttempts($ip);
    }
    
    public function addBan($ip, $username){
        $model = new \App\Base\Model(array('ip' => $ip, 'username' => $username));
        $this->mapper->insert($model);
    }
    
    public function isBlocked($ip){
        return $this->getFailedLoginAttempts($ip) > self::$MAX_ATTEMPTS;
    }

}
