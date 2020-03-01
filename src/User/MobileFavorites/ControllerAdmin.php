<?php

namespace App\User\MobileFavorites;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Main\Helper;
use App\Activity\Controller as Activity;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;

class ControllerAdmin extends \App\Base\Controller {

    protected $model = '\App\User\MobileFavorites\MobileFavorite';
    protected $parent_model = '\App\User\User';
    protected $index_route = 'users_mobile_favorites_admin';
    protected $edit_template = 'profile/mobile_favorites/edit.twig';
    protected $element_view_route = 'users_mobile_favorites_edit_admin';
    // use user from attribute instead of the current logged in user
    // when saving new entries
    protected $user_from_attribute = true;

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation, CurrentUser $current_user) {
        parent::__construct($logger, $twig, $helper, $flash, $router, $settings, $db, $activity, $translation, $current_user);


        $this->mapper = new Mapper($this->db, $this->translation, $current_user);
    }

    public function index(Request $request, Response $response) {

        $user_id = $request->getAttribute('user');
        $user = null;
        if (!is_null($user_id)) {
            $user = $this->user_mapper->get($user_id);
            $this->mapper->setUser($user->id);
        }

        $list = $this->mapper->getAll('position');
        return $this->twig->render($response, 'profile/mobile_favorites/index.twig', ['list' => $list, 'for_user' => $user]);
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

        return $this->twig->render($response, $this->edit_template, ['entry' => $entry, 'for_user' => $user]);
    }

    protected function afterSave($id, array $data, Request $request) {
        // redirect to users list
        $this->index_params = ["user" => $data["user"]];
    }

    protected function getElementViewRoute($entry) {
        $user = $this->user_mapper->get($entry->user);
        $this->element_view_route_params["user"] = $user->id;
        return parent::getElementViewRoute($entry);
    }

}
