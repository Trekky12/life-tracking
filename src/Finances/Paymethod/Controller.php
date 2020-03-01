<?php

namespace App\Finances\Paymethod;

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

    protected $model = '\App\Finances\Paymethod\Paymethod';
    protected $index_route = 'finances_paymethod';
    protected $edit_template = 'finances/paymethod/edit.twig';
    protected $element_view_route = 'finances_paymethod_edit';
    protected $module = "finances";

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation, CurrentUser $current_user) {
        parent::__construct($logger, $twig, $helper, $flash, $router, $settings, $db, $activity, $translation, $current_user);


        $this->mapper = new Mapper($this->db, $this->translation, $current_user);
    }

    public function index(Request $request, Response $response) {
        $paymethods = $this->mapper->getAll('name');
        return $this->twig->render($response, 'finances/paymethod/index.twig', ['paymethods' => $paymethods]);
    }

    protected function afterSave($id, array $data, Request $request) {
        $method = $this->mapper->get($id);

        // Set all other non-default, since there can only be one default category
        if ($method->is_default == 1) {
            $this->mapper->unset_default($id);
        }

        // when there is no default make this the default
        $default = $this->mapper->get_default();
        if (is_null($default)) {
            $this->mapper->set_default($id);
        }
    }

}
