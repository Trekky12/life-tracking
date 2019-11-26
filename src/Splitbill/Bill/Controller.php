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
        $this->paymethod_mapper = new \App\Finances\Paymethod\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {

        $hash = $request->getAttribute('group');
        $group = $this->group_mapper->getFromHash($hash);

        $this->checkAccess($group->id);

        $list = $this->mapper->getTableData($group->id, 0, 'DESC', 10);
        $table = $this->renderTableRows($group, $list);
        $datacount = $this->mapper->tableCount($group->id);

        $users = $this->user_mapper->getAll();

        list($balance, $my_balance) = $this->calculateBalance($group->id);

        return $this->ci->view->render($response, 'splitbills/bills/index.twig', [
                    "bills" => $table,
                    "group" => $group,
                    "datacount" => $datacount,
                    "balance" => $balance,
                    "my_balance" => $my_balance,
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

        // GET Param 'type'
        $type = $request->getParam('type');

        $hash = $request->getAttribute('group');
        $group = $this->group_mapper->getFromHash($hash);

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }
        $this->preEdit($entry_id, $request);

        $users = $this->user_mapper->getAll();
        $group_users = $this->group_mapper->getUsers($group->id);

        $balance = $this->mapper->getBalance($entry_id);

        $totalValue = $this->mapper->getBillSpend($entry_id);

        $paymethods = $this->paymethod_mapper->getAllfromUsers($group_users);

        return $this->ci->view->render($response, $this->edit_template, [
                    'entry' => $entry,
                    'group' => $group,
                    'group_users' => $group_users,
                    'users' => $users,
                    'balance' => $balance,
                    'totalValue' => $totalValue,
                    'type' => $type,
                    'paymethods' => $paymethods
        ]);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $hash = $request->getAttribute('group');
        $data = $request->getParsedBody();
        $data['user'] = $this->ci->get('helper')->getUser()->id;

        $this->insertOrUpdate($id, $data, $request);

        return $response->withRedirect($this->ci->get('router')->pathFor($this->index_route, ["group" => $hash]), 301);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, &$data, Request $request) {
        $this->allowOwnerOnly($id);
    }

    protected function preEdit($id, Request $request) {
        $this->allowOwnerOnly($id);
    }

    protected function preDelete($id, Request $request) {
        $this->allowOwnerOnly($id);
        
        $bill = $this->mapper->get($id);
        $sbgroup = $this->group_mapper->get($bill->sbgroup);
        $existing_balance = $this->mapper->getBalance($bill->id);

        $users = $this->user_mapper->getAll();

        $this->notifyUsers("delete", $bill, $sbgroup, $existing_balance, $users);
    }

    /**
     * Save balance
     */
    protected function afterSave($id, $data, Request $request) {

        $logger = $this->ci->get('logger');

        $bill = $this->mapper->get($id);
        $sbgroup = $this->group_mapper->get($bill->sbgroup);
        $existing_balance = $this->mapper->getBalance($bill->id);

        $users = $this->user_mapper->getAll();

        // Save Balance
        if (array_key_exists("balance", $data) && is_array($data["balance"])) {

            $splitbill_groups_users = $this->group_mapper->getUsers($bill->sbgroup);
            $removed_users = array_diff(array_keys($existing_balance), $splitbill_groups_users);

            list($balances, $sum_paid, $sum_spend, $totalValue) = $this->filterBalances($data, $splitbill_groups_users);

            // floating point comparison
            if (!empty($balances) && $totalValue > 0 && (abs(($totalValue - $sum_paid) / $totalValue) < 0.00001) && (abs(($totalValue - $sum_spend) / $totalValue) < 0.00001)) {
                $logger->addInfo('Add balance for bill', array("bill" => $id, "balances" => $balances));

                $this->addBalancesForUsers($bill, $sbgroup, $balances, $totalValue, $users);

                // delete entries for users removed from the group
                foreach ($removed_users as $ru) {
                    $this->mapper->deleteBalanceofUser($bill->id, $ru);
                }
            } else if ($totalValue > 0) {
                $logger->addError('Balance for bill wrong', array("bill" => $bill, "data" => $data));

                // there was an error with the balance, so delete the bill
                $has_balance = count($existing_balance) > 0;
                // delete the bill only when there are no existing balance entries (new bill)
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

        $this->notifyUsers("edit", $bill, $sbgroup, $existing_balance, $users);
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
            if ($bill->settleup == 1) {
                $row[] = $bill->spend; // received
                $row[] = null;
                $row[] = $bill->paid;
                $row[] = null;
            } else {
                $row[] = null;
                $row[] = $bill->spend;
                $row[] = $bill->paid;
                $row[] = $bill->balance;
            }

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
        $settled = $this->mapper->getSettledUpSpendings($group, 1);

        $me = intval($this->ci->get('helper')->getUser()->id);

        if (!array_key_exists($me, $balance)) {
            return array($balance, null);
        }

        $my_balance = $balance[$me]["balance"];

        foreach ($balance as $user_id => &$b) {

            $b["settled"] = array_key_exists($user_id, $settled) ? $settled[$user_id] : 0;

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

        $filtered = array_filter($balance, function($b) use ($me) {
            return $b["user"] != $me && ($b['balance'] != 0 or $b['owe'] != 0);
        });
        /**
         * Resort Balances
         * Big credits on top, big debits on top 
         */
        uasort($filtered, function($a, $b) {
            if ($b['owe'] > 0) {
                return $b['owe'] - $a['owe'];
            }
            return $a['owe'] - $b['owe'];
        });

        $my_balance_overview = array_key_exists($me, $balance) ? $balance[$me] : null;
        return array($filtered, $my_balance_overview);
    }

    private function filterBalances($data, $group_users) {
        $totalValue = array_key_exists("value", $data) ? floatval(filter_var($data["value"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0;

        $balances = [];
        $sum_paid = 0;
        $sum_spend = 0;
        foreach ($data["balance"] as $user_id => $bdata) {
            $user = intval(filter_var($user_id, FILTER_SANITIZE_NUMBER_INT));

            if (in_array($user, $group_users)) {
                $spend = array_key_exists("spend", $bdata) ? floatval(filter_var($bdata["spend"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0;
                $paid = array_key_exists("paid", $bdata) ? floatval(filter_var($bdata["paid"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0;
                $paymethod = array_key_exists("paymethod", $bdata) && !empty($bdata["paymethod"]) ? intval(filter_var($bdata["paymethod"], FILTER_SANITIZE_NUMBER_INT)) : null;

                $sum_paid += $paid;
                $sum_spend += $spend;

                // add entry
                $balances[] = ["user" => $user, "spend" => $spend, "paid" => $paid, "paymethod" => $paymethod];
            }
        }
        return array($balances, $sum_paid, $sum_spend, $totalValue);
    }

    private function addBalancesForUsers($bill, $group, $balances, $totalValue, $users) {
        $finance_mapper = new \App\Finances\Mapper($this->ci);
        $finance_ctrl = new \App\Finances\Controller($this->ci);

        foreach ($balances as $b) {
            $this->mapper->addOrUpdateBalance($bill->id, $b["user"], $b["paid"], $b["spend"], $b["paymethod"]);

            $userObj = $users[$b["user"]];

            if ($group->add_finances > 0 && $bill->settleup != 1 && $userObj->module_finance == 1) {
                if ($b["spend"] > 0) {
                    $entry = new \App\Finances\FinancesEntry([
                        "date" => $bill->date,
                        "time" => $bill->time,
                        "description" => $bill->name,
                        "type" => 0,
                        "value" => $b["spend"],
                        "user" => $b["user"],
                        "common" => 1,
                        "common_value" => $totalValue,
                        "bill" => $bill->id,
                        "lng" => $bill->lng,
                        "lat" => $bill->lat,
                        "acc" => $bill->acc,
                        "paymethod" => $b["paymethod"]
                    ]);

                    $entry->category = $finance_ctrl->getDefaultOrAssignedCategory($b["user"], $entry);
                    $finance_mapper->addOrUpdateFromBill($entry);
                } else {
                    $finance_mapper->deleteEntrywithBill($bill->id, $b["user"]);
                }
            }
        }
    }

    private function notifyUsers($type, $bill, $sbgroup, $existing_balance, $users) {
        /**
         * Notify users
         */
        $me = $this->ci->get('helper')->getUser();
        $my_user_id = intval($me->id);
        $users_afterSave = $this->mapper->getBillUsers($bill->id);

        $new_balances = $this->mapper->getBalance($bill->id);
        $billValue = $this->mapper->getBillSpend($bill->id);

        $group_path = $this->ci->get('router')->pathFor('splitbill_bills', array('group' => $sbgroup->hash));
        $group_url = $this->ci->get('helper')->getPath() . $group_path;

        $is_new_bill = count($existing_balance) == 0;

        if ($bill->settleup === 0) {

            if ($type == "edit") {
                $subject1 = $this->ci->get('helper')->getTranslatedString('MAIL_SPLITTED_BILL_ADDED_SUBJECT');
                $content1 = $this->ci->get('helper')->getTranslatedString('MAIL_SPLITTED_BILL_ADDED_DETAIL');
                if (!$is_new_bill) {
                    $subject1 = $this->ci->get('helper')->getTranslatedString('MAIL_SPLITTED_BILL_UPDATE_SUBJECT');
                    $content1 = $this->ci->get('helper')->getTranslatedString('MAIL_SPLITTED_BILL_UPDATE_DETAIL');
                }
            } else {
                $subject1 = $this->ci->get('helper')->getTranslatedString('MAIL_SPLITTED_BILL_DELETED_SUBJECT');
                $content1 = $this->ci->get('helper')->getTranslatedString('MAIL_SPLITTED_BILL_DELETED_DETAIL');
            }

            $subject = sprintf($subject1, $bill->name);
            $content = sprintf($content1, $me->name, $bill->name, $billValue, $sbgroup->currency, $group_url, $sbgroup->name);
            $lang_spend = $this->ci->get('helper')->getTranslatedString('SPEND');
            $lang_paid = $this->ci->get('helper')->getTranslatedString('PAID');
        } else {
            if ($type == "edit") {
                $subject1 = $this->ci->get('helper')->getTranslatedString('MAIL_SPLITTED_BILL_SETTLEUP_SUBJECT');
                $content1 = $this->ci->get('helper')->getTranslatedString('MAIL_SPLITTED_BILL_SETTLEUP_DETAIL');
                if (!$is_new_bill) {
                    $subject1 = $this->ci->get('helper')->getTranslatedString('MAIL_SPLITTED_BILL_SETTLEUP_UPDATE_SUBJECT');
                    $content1 = $this->ci->get('helper')->getTranslatedString('MAIL_SPLITTED_BILL_SETTLEUP_UPDATE_DETAIL');
                }
            } else {
                $subject1 = $this->ci->get('helper')->getTranslatedString('MAIL_SPLITTED_BILL_SETTLEUP_DELETED_SUBJECT');
                $content1 = $this->ci->get('helper')->getTranslatedString('MAIL_SPLITTED_BILL_SETTLEUP_DELETED_DETAIL');
            }

            $subject = sprintf($subject1, $me->name);
            $content = sprintf($content1, $me->name, $billValue, $sbgroup->currency, $group_url, $sbgroup->name);
            $lang_spend = $this->ci->get('helper')->getTranslatedString('SPLITBILLS_SETTLE_UP_SENDER');
            $lang_paid = $this->ci->get('helper')->getTranslatedString('SPLITBILLS_SETTLE_UP_RECEIVER');
        }

        $notificationCtrl = new \App\Notifications\Controller($this->ci);

        foreach ($users_afterSave as $nu) {

            // except self
            if ($nu !== $my_user_id) {
                $user = $this->user_mapper->get($nu);

                // Mail
                if ($user->mail && $user->mails_splitted_bills == 1) {

                    $variables = array(
                        'header' => '',
                        'subject' => $subject,
                        'headline' => sprintf($this->ci->get('helper')->getTranslatedString('HELLO') . ' %s', $user->name),
                        'content' => $content,
                        'currency' => $sbgroup->currency,
                        'balances' => $new_balances,
                        'users' => $users,
                        'LANG_SPEND' => $lang_spend,
                        'LANG_PAID' => $lang_paid,
                    );

                    $this->ci->get('helper')->send_mail('mail/splitted_bill.twig', $user->mail, $subject, $variables);
                }

                // Notification
                $notificationCtrl->sendNotificationsToUserWithCategory($user->id, "NOTIFICATION_CATEGORY_SPLITTED_BILLS", $subject, $content, $group_path);
            }
        }
    }

}
