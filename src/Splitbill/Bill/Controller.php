<?php

namespace App\Splitbill\Bill;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Splitbill\Bill\Bill';
        $this->index_route = 'splitbill_bills';
        $this->edit_template = 'splitbills/bills/edit.twig';

        $this->mapper = new Mapper($this->ci);
        $this->group_mapper = new \App\Splitbill\Group\Mapper($this->ci);
        $this->user_mapper = new \App\User\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {

        $hash = $request->getAttribute('group');
        $group = $this->group_mapper->getFromHash($hash);

        $this->checkAccess($group->id);

        $list = $this->mapper->getTableData($group->id, 0, 'DESC', 10);
        $table = $this->renderTableRows($group, $list);
        $datacount = $this->mapper->tableCount($group->id);

        $users = $this->user_mapper->getAll();

        $balance = $this->calculateBalance($group->id);

        return $this->ci->view->render($response, 'splitbills/bills/index.twig', [
                    "bills" => $table,
                    "group" => $group,
                    "datacount" => $datacount,
                    "balance" => $balance,
                    "hasSplitbillTable" => true,
                    "currency" => $this->ci->get('settings')['app']['i18n']['currency'],
                    "users" => $users
        ]);
    }

    public function table(Request $request, Response $response) {

        $requestData = $request->getQueryParams();

        $hash = $request->getAttribute('group');
        $group = $this->group_mapper->getFromHash($hash);

        $this->checkAccess($group->id);

        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? filter_var($requestData["searchQuery"], FILTER_SANITIZE_STRING) : null;
        $searchQuery = empty($search) || $search === "null" ? "%" : "%" . $search . "%";

        $sortColumnIndex = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortDirection = array_key_exists("sortDirection", $requestData) ? filter_var($requestData["sortDirection"], FILTER_SANITIZE_STRING) : null;

        $recordsTotal = $this->mapper->tableCount($group->id);
        $recordsFiltered = $this->mapper->tableCount($group->id, $searchQuery);

        $data = $this->mapper->getTableData($group->id, $sortColumnIndex, $sortDirection, $length, $start, $searchQuery);
        $rendered_data = $this->renderTableRows($group, $data);

        return $response->withJson([
                    "recordsTotal" => intval($recordsTotal),
                    "recordsFiltered" => intval($recordsFiltered),
                    "data" => $rendered_data
        ]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $hash = $request->getAttribute('group');
        $group = $this->group_mapper->getFromHash($hash);

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }
        $this->preEdit($entry_id);

        $users = $this->user_mapper->getAll();
        $group_users = $this->group_mapper->getUsers($group->id);

        $balance = $this->mapper->getBalance($entry_id);

        $totalValue = $this->mapper->getBillSpend($entry_id);

        return $this->ci->view->render($response, $this->edit_template, [
                    'entry' => $entry,
                    'group' => $group,
                    'group_users' => $group_users,
                    'users' => $users,
                    'balance' => $balance,
                    'totalValue' => $totalValue
        ]);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $hash = $request->getAttribute('group');
        $data = $request->getParsedBody();
        $data['user'] = $this->ci->get('helper')->getUser()->id;

        $this->insertOrUpdate($id, $data);

        return $response->withRedirect($this->ci->get('router')->pathFor($this->index_route, ["group" => $hash]), 301);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, &$data) {
        $this->allowOwnerOnly($id);
    }

    protected function preEdit($id) {
        $this->allowOwnerOnly($id);
    }

    protected function preDelete($id) {
        $this->allowOwnerOnly($id);
    }

    /**
     * Save balance
     */
    protected function afterSave($id, $data) {

        $logger = $this->ci->get('logger');

        if (array_key_exists("balance", $data) && is_array($data["balance"])) {
            $bill = $this->mapper->get($id);
            $splitbill_groups_users = $this->group_mapper->getUsers($bill->sbgroup);
            $existing_balance = $this->mapper->getBalance($bill->id);
            $sbgroup = $this->group_mapper->get($bill->sbgroup);

            $totalValue = array_key_exists("balance", $data) ? floatval(filter_var($data["value"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0;

            $sum_paid = 0;
            $sum_spend = 0;
            $balances = [];
            foreach ($data["balance"] as $user_id => $bdata) {
                $user = intval(filter_var($user_id, FILTER_SANITIZE_NUMBER_INT));

                if (in_array($user, $splitbill_groups_users)) {
                    $spend = array_key_exists("spend", $bdata) ? floatval(filter_var($bdata["spend"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0;
                    $paid = array_key_exists("paid", $bdata) ? floatval(filter_var($bdata["paid"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0;

                    $sum_paid += $paid;
                    $sum_spend += $spend;

                    // add entry
                    $balances[] = ["user" => $user, "spend" => $spend, "paid" => $paid];
                }
            }

            // floating point comparison
            if (!empty($balances) && (abs(($totalValue - $sum_paid) / $totalValue) < 0.00001) && (abs(($totalValue - $sum_spend) / $totalValue) < 0.00001)) {
                $logger->addInfo('Add balance for bill', array("bill" => $id, "balances" => $balances));
                
                foreach ($balances as $b) {
                    $this->mapper->addOrUpdateBalance($bill->id, $b["user"], $b["paid"], $b["spend"]);
                }

                // delete entries for users removed from the group
                $removed_users = array_diff(array_keys($existing_balance), $splitbill_groups_users);
                foreach ($removed_users as $ru) {
                    $this->mapper->deleteBalanceofUser($bill->id, $ru);
                }
            } else {
                $logger->addError('Balance for bill wrong', array("bill" => $bill, "data" => $data));

                // there was an error with the balance, so delete the bill
                $has_balance = count($existing_balance) > 0;
                // delete the bill only when there are no existing balance entries 
                if (!$has_balance) {
                    $logger->addWarning('delete bill', array("bill" => $bill, "data" => $data));
                    $this->mapper->delete($bill->id);
                }

                // delete success message of bill
                $this->ci->get('flash')->clearMessage('message');

                // add error message
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("SPLITBILLS_BILL_ERROR"));
                $this->ci->get('flash')->addMessage('message_type', 'danger');
            }
        }
    }

    /**
     * Is the user allowed to view this crawler?
     */
    private function checkAccess($id) {
        $splitbill_groups_users = $this->group_mapper->getUsers($id);
        $user = $this->ci->get('helper')->getUser()->id;
        if (!in_array($user, $splitbill_groups_users)) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
        }
    }

    private function renderTableRows($group, array $bills) {
        $user = $this->ci->get('helper')->getUser()->id;

        $rendered_data = [];
        foreach ($bills as $bill) {
            $row = [];
            $row[] = $bill->date;
            $row[] = $bill->time;
            $row[] = $bill->name;
            $row[] = $bill->spend;
            $row[] = $bill->paid;
            $row[] = $bill->balance;

            if ($bill->user == $user) {
                $row[] = '<a href="' . $this->ci->get('router')->pathFor('splitbill_bills_edit', ['id' => $bill->id, 'group' => $group->hash]) . '"><span class="fa fa-pencil-square-o fa-lg"></span></a>';
                $row[] = '<a href="#" data-url="' . $this->ci->get('router')->pathFor('splitbill_bills_delete', ['id' => $bill->id, 'group' => $group->hash]) . '" class="btn-delete"><span class="fa fa-trash fa-lg"></span></a>';
            }

            $rendered_data[] = $row;
        }
        return $rendered_data;
    }

    private function calculateBalance($group) {
        $balance = $this->mapper->getTotalBalance($group);

        $me = intval($this->ci->get('helper')->getUser()->id);

        if(!array_key_exists($me, $balance)){
            return $balance;
        }
        
        $my_balance = $balance[$me]["balance"];

        foreach ($balance as $user_id => &$b) {
            if ($user_id !== $me) {

                // i owe money
                if ($my_balance < 0 && $b["balance"] > 0) {

                    // another person owes the user money
                    // but my debit is now settled
                    if ($b["balance"] > abs($my_balance)) {
                        $b["owe"] = -1 * $my_balance;
                        $my_balance = 0;
                    }
                    // I'm the only one who owes this user money
                    // my debit is now lower
                    else {
                        $b["owe"] = $b["balance"];
                        $my_balance = $my_balance - $b["balance"];
                    }
                }


                // someone owes me money
                if ($my_balance > 0 && $b["balance"] < 0) {

                    // another user owes me money
                    if ($my_balance > abs($b["balance"])) {
                        $b["owe"] = $b["balance"];
                        $my_balance = $my_balance - $b["balance"];
                    }
                    // only this user owes me money
                    // my credit is settled
                    else {
                        $b["owe"] = -1 * $my_balance;
                        $my_balance = 0;
                    }
                }
            }
        }

        /**
         * Resort Balances
         * Big credits on top, big debits on top 
         */
        uasort($balance, function($a, $b) {
            if ($b['owe'] > 0) {
                return $b['owe'] - $a['owe'];
            }
            return $a['owe'] - $b['owe'];
        });
        return $balance;
    }

}
