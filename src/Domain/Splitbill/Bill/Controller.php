<?php

namespace App\Domain\Splitbill\Bill;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Splitbill\Group\SplitbillGroupService;
use App\Domain\Finances\Paymethod\PaymethodService;
use App\Domain\User\UserService;

class Controller extends \App\Domain\Base\Controller {

    private $group_service;
    private $paymethod_service;
    private $user_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            SplitbillBillService $service,
            SplitbillGroupService $group_service,
            PaymethodService $paymethod_service,
            UserService $user_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->service->setParentObjectService($group_service);

        $this->group_service = $group_service;

        $this->paymethod_service = $paymethod_service;

        $this->user_service = $user_service;
    }

    public function index(Request $request, Response $response) {

        $hash = $request->getAttribute('group');
        $group = $this->group_service->getFromHash($hash);

        if (!$this->group_service->isMember($group->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $table = $this->service->getTableDataIndex($group);

        return $this->twig->render($response, 'splitbills/bills/index.twig', $table);
    }

    public function table(Request $request, Response $response) {

        $requestData = $request->getQueryParams();

        $hash = $request->getAttribute('group');
        $group = $this->group_service->getFromHash($hash);

        if (!$this->group_service->isMember($group->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $response_data = $this->service->getTableData($group, $requestData);

        return $response->withJson($response_data);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        // GET Param 'type'
        $type = $request->getParam('type');

        $hash = $request->getAttribute('group');
        $group = $this->group_service->getFromHash($hash);

        if (!$this->group_service->isMember($group->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $entry = $this->service->getEntry($entry_id);

        $users = $this->user_service->getAll();
        $group_users = $this->group_service->getUsers($group->id);

        list($balance, $totalValue, $totalValueForeign) = $this->service->getBillbalance($entry_id);

        $paymethods = $this->paymethod_service->getAllfromUsers($group_users);

        return $this->twig->render($response, 'splitbills/bills/edit.twig', [
                    'entry' => $entry,
                    'group' => $group,
                    'group_users' => $group_users,
                    'users' => $users,
                    'balance' => $balance,
                    'totalValue' => $totalValue,
                    'type' => $type,
                    'paymethods' => $paymethods,
                    'totalValueForeign' => $totalValueForeign
        ]);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        $sbgroup_hash = $request->getAttribute("group");
        $group = $this->group_service->getFromHash($sbgroup_hash);

        if (!$this->group_service->isMember($group->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $data["sbgroup"] = $group->id;

        $new_id = $this->doSave($id, $data, null);

        /**
         * save Balance
         */
        $bill = $this->service->getEntry($new_id);

        list($existing_balance, $totalValue, $totalValueForeign) = $this->service->getBillbalance($bill->id);

        // Save Balance
        if (array_key_exists("balance", $data) && is_array($data["balance"])) {

            $splitbill_groups_users = $this->group_service->getUsers($bill->sbgroup);

            $add_balance = $this->service->addBalances($bill, $group, $splitbill_groups_users, $data);

            // Balance was wrong!
            // delete success message of bill
            if (!$add_balance) {

                $this->flash->clearMessage('message');

                // add error message
                $this->flash->addMessage('message', $this->translation->getTranslatedString("SPLITBILLS_BILL_ERROR"));
                $this->flash->addMessage('message_type', 'danger');
            }
        }

        $this->service->notifyUsers("edit", $bill, $group, $existing_balance);

        $redirect_url = $this->router->urlFor('splitbill_bills', ["group" => $sbgroup_hash]);
        return $response->withRedirect($redirect_url, 301);
    }

    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');

        $sbgroup_hash = $request->getAttribute("group");
        $group = $this->group_service->getFromHash($sbgroup_hash);

        if (!$this->group_service->isMember($group->id)) {
            $response_data = ['is_deleted' => false, 'error' => $this->translation->getTranslatedString('NO_ACCESS')];
        } else {

            $bill = $this->service->getEntry($id);
            list($existing_balance, $totalValue, $totalValueForeign) = $this->service->getBillbalance($id);

            $this->service->notifyUsers("delete", $bill, $group, $existing_balance);

            $response_data = $this->doDelete($id);
        }
        return $response->withJson($response_data);
    }

}
