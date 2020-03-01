<?php

namespace App\Finances\Assignment;

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

    protected $model = '\App\Finances\Assignment\Assignment';
    protected $index_route = 'finances_categories_assignment';
    protected $element_view_route = 'finances_categories_assignment_edit';
    protected $module = "finances";
    private $cat_mapper;

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation, CurrentUser $current_user) {
        parent::__construct($logger, $twig, $helper, $flash, $router, $settings, $db, $activity, $translation, $current_user);


        $this->mapper = new Mapper($this->db, $this->translation, $current_user);
        $this->cat_mapper = new \App\Finances\Category\Mapper($this->db, $this->translation, $current_user);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        $categories = $this->cat_mapper->getAll('name');

        return $this->twig->render($response, 'finances/assignment/edit.twig', ['entry' => $entry, 'categories' => $categories]);
    }

    public function index(Request $request, Response $response) {
        $assignments = $this->mapper->getAll('description');
        $categories = $this->cat_mapper->getAll();
        return $this->twig->render($response, 'finances/assignment/index.twig', ['assignments' => $assignments, 'categories' => $categories]);
    }

}
