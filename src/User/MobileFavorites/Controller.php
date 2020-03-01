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

class Controller extends \App\Base\Controller {

    protected $model = '\App\User\MobileFavorites\MobileFavorite';
    protected $index_route = 'users_mobile_favorites';
    protected $edit_template = 'profile/mobile_favorites/edit.twig';
    protected $element_view_route = 'users_mobile_favorites_edit';

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation, CurrentUser $current_user) {
        parent::__construct($logger, $twig, $helper, $flash, $router, $settings, $db, $activity, $translation, $current_user);


        $this->mapper = new Mapper($this->db, $this->translation, $current_user);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getAll('position');
        return $this->twig->render($response, 'profile/mobile_favorites/index.twig', ['list' => $list]);
    }

}
