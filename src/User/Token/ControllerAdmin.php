<?php

namespace App\User\Token;

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

    protected $index_route = 'login_tokens';

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation, CurrentUser $current_user) {
        parent::__construct($logger, $twig, $helper, $flash, $router, $settings, $db, $activity, $translation, $current_user);


        $this->mapper = new Mapper($this->db, $this->translation, $current_user);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getAll();
        $users = $this->user_mapper->getAll();
        return $this->twig->render($response, 'user/tokens.twig', ['list' => $list, 'users' => $users]);
    }

    public function deleteOld(Request $request, Response $response) {
        $this->mapper->deleteOldTokens();
        return $response->withRedirect($this->router->urlFor($this->index_route), 301);
    }

    public function deleteOldTokens() {
        return $this->mapper->deleteOldTokens();
    }

}
