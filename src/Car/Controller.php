<?php

namespace App\Car;

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

    protected $model = '\App\Car\Car';
    protected $index_route = 'cars';
    protected $edit_template = 'cars/control/edit.twig';
    protected $element_view_route = 'cars_edit';
    protected $module = "cars";

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation, CurrentUser $current_user) {
        parent::__construct($logger, $twig, $helper, $flash, $router, $settings, $db, $activity, $translation, $current_user);

        $this->mapper = new Mapper($this->db, $this->translation, $current_user);
    }

    public function index(Request $request, Response $response) {
        $cars = $this->mapper->getAll('name');
        return $this->twig->render($response, 'cars/control/index.twig', ['cars' => $cars]);
    }

}
