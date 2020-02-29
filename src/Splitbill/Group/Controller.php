<?php

namespace App\Splitbill\Group;

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
use Hashids\Hashids;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Splitbill\Group\Group';
    protected $index_route = 'splitbills';
    protected $edit_template = 'splitbills/groups/edit.twig';
    protected $element_view_route = 'splitbill_groups_edit';
    protected $module = "splitbills";
    private $bill_mapper;

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, UserHelper $user_helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation) {
        parent::__construct($logger, $twig, $helper, $user_helper, $flash, $router, $settings, $db, $activity, $translation);

        $user = $this->user_helper->getUser();

        $this->mapper = new Mapper($this->db, $this->translation, $user);
        $this->bill_mapper = new \App\Splitbill\Bill\Mapper($this->db, $this->translation, $user);
    }

    public function index(Request $request, Response $response) {
        $groups = $this->mapper->getUserItems('t.createdOn DESC, name');
        $balances = $this->bill_mapper->getBalances();
        return $this->twig->render($response, 'splitbills/groups/index.twig', ['groups' => $groups, 'balances' => $balances]);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, array &$data, Request $request) {
        $this->allowOwnerOnly($id);
    }

    protected function preEdit($id, Request $request) {
        $this->allowOwnerOnly($id);
    }

    protected function preDelete($id, Request $request) {
        $this->allowOwnerOnly($id);
    }

    protected function afterSave($id, array $data, Request $request) {
        $dataset = $this->mapper->get($id);
        if (empty($dataset->getHash())) {
            $hashids = new Hashids('', 10);
            $hash = $hashids->encode($id);
            $this->mapper->setHash($id, $hash);
        }
    }

}
