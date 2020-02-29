<?php

namespace App\Notifications\Categories;

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

class Controller extends \App\Base\Controller {

    protected $model = '\App\Notifications\Categories\Category';
    protected $index_route = 'notifications_categories';
    protected $edit_template = 'notifications/categories/edit.twig';
    protected $element_view_route = 'notifications_categories_edit';
    protected $module = "notifications";

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, UserHelper $user_helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation) {
        parent::__construct($logger, $twig, $helper, $user_helper, $flash, $router, $settings, $db, $activity, $translation);

        $user = $this->user_helper->getUser();

        $this->mapper = new Mapper($this->db, $this->translation, $user);
    }

    public function index(Request $request, Response $response) {
        $categories = $this->mapper->getAll('name');
        $categories_filtered = array_filter($categories, function($cat) {
            return !$cat->isInternal();
        });
        return $this->twig->render($response, 'notifications/categories/index.twig', ['categories' => $categories_filtered]);
    }

    protected function preEdit($id, Request $request) {
        $this->checkAccess($id);
    }

    protected function preSave($id, array &$data, Request $request) {
        $this->checkAccess($id);
    }

    private function checkAccess($id) {
        if (!is_null($id)) {
            $cat = $this->mapper->get($id);
            if ($cat->isInternal()) {
                throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

}
