<?php

namespace App\User\MobileFavorites;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ControllerAdmin extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\User\MobileFavorites\MobileFavorite';
        $this->index_route = 'users_mobile_favorites_admin';
        $this->edit_template = 'profile/mobile_favorites/edit.twig';

        $this->mapper = new Mapper($this->ci);

        $this->user_mapper = new \App\User\Mapper($this->ci);

        // use user from attribute instead of the current logged in user
        // when saving new entries
        $this->user_from_attribute = true;
    }

    public function index(Request $request, Response $response) {

        $user_id = $request->getAttribute('user');
        $user = null;
        if (!is_null($user_id)) {
            $user = $this->user_mapper->get($user_id);
            $this->mapper->setUser($user->id);
        }

        $list = $this->mapper->getAll('position');
        return $this->ci->view->render($response, 'profile/mobile_favorites/index.twig', ['list' => $list, 'for_user' => $user]);
    }

    public function edit(Request $request, Response $response) {

        // get user and change filter to this user
        $user_id = $request->getAttribute('user');
        $user = null;
        if (!is_null($user_id)) {
            $user = $this->user_mapper->get($user_id);
            $this->mapper->setUser($user->id);
        }

        // load entry
        $entry_id = $request->getAttribute('id');
        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }
        $this->preEdit($entry_id, $request);

        return $this->ci->view->render($response, $this->edit_template, ['entry' => $entry, 'for_user' => $user]);
    }

    protected function afterSave($id, $data, Request $request) {
        // redirect to users list
        $this->index_params = ["user" => $data["user"]];
    }

}
