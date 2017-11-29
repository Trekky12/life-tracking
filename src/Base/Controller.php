<?php

namespace App\Base;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

abstract class Controller {

    protected $ci;
    protected $mapper;
    protected $model;
    protected $index_route;
    protected $edit_template;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
        $this->init();
    }

    /**
     * Initialize the main variables
     * @var $model
     * @var $index
     * @var $edit_template;
     */
    abstract function init();
    
    /**
     * this function is called after successfully saving an entry
     * @param type $id
     */
    protected function afterSave($id){
        // do nothing
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        
        $data['user'] = $this->ci->get('helper')->getUser()->id;       

        $entry = new $this->model($data);
        
        if ($entry->hasParsingErrors()) {
            $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString($entry->getParsingErrors()[0]));
            $this->ci->get('flash')->addMessage('message_type', 'danger');
        } else {

            if ($id == null) {
                $id = $this->mapper->insert($entry);
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_SUCCESS_ADD"));
            } else {
                $this->mapper->update($entry);
                $this->ci->get('flash')->addMessage('message',$this->ci->get('helper')->getTranslatedString("ENTRY_SUCCESS_UPDATE"));
            }
            
            $this->afterSave($id);

            $this->ci->get('flash')->addMessage('message_type', 'success');
        }

        return $response->withRedirect($this->ci->get('router')->pathFor($this->index_route), 301);
    }

    public function delete(Request $request, Response $response) {

        $id = $request->getAttribute('id');
        $data = ['is_deleted' => false, 'error' => ''];

        try {
            $is_deleted = $this->mapper->delete($id);
            $data ['is_deleted'] = $is_deleted;
            if ($is_deleted) {
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_SUCCESS_DELETE"));
                $this->ci->get('flash')->addMessage('message_type', 'success');
            } else {
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_ERROR_DELETE"));
                $this->ci->get('flash')->addMessage('message_type', 'danger');
            }
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
            $this->ci->get('flash')->addMessage('message',$this->ci->get('helper')->getTranslatedString("ENTRY_ERROR_DELETE"));
            $this->ci->get('flash')->addMessage('message_type', 'danger');
        }

        $newResponse = $response->withJson($data);

        return $newResponse;
    }
    
    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        return $this->ci->view->render($response, $this->edit_template, ['entry' => $entry]);
    }

}
