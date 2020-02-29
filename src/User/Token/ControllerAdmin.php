<?php

namespace App\User\Token;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Main\Helper;
use App\Main\UserHelper;
use App\Activity\Controller as Activity;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;

class ControllerAdmin extends \App\Base\Controller {

    protected $index_route = 'login_tokens';

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, UserHelper $user_helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation) {
        parent::__construct($logger, $twig, $helper, $user_helper, $flash, $router, $settings, $db, $activity, $translation);

        $user = $this->user_helper->getUser();

        $this->mapper = new Mapper($this->db, $this->translation, $user);
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
