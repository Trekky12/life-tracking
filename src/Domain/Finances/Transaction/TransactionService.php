<?php

namespace App\Domain\Finances\Transaction;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Finances\Account\AccountService;
use App\Domain\Main\Utility\Utility;
use Slim\Routing\RouteParser;

class TransactionService extends Service {

    private $account_service;
    private $router;

    public function __construct(LoggerInterface $logger, 
        CurrentUser $user, 
        TransactionMapper $mapper,
        AccountService $account_service,
        RouteParser $router) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->account_service = $account_service;
        $this->router = $router;
    }

    public function index($account_hash, $count = 10) {

        $account = $this->account_service->getFromHash($account_hash);

        if(!$this->account_service->isOwner($account->id)){
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $list = $this->getMapper()->getTableData($account->id, 0, 'DESC', $count);
        $table = $this->renderTableRows($account, $list);
        $datacount = $this->getMapper()->tableCount($account->id);

        return new Payload(Payload::$RESULT_HTML, [
            "account" => $account,
            "list" => $table,
            "datacount" => $datacount,
            "hasFinanceTransactionTable" => true
        ]);
    }
    public function table($account_hash, $requestData): Payload {

        $account = $this->account_service->getFromHash($account_hash);

        if(!$this->account_service->isOwner($account->id)){
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;
        $search = array_key_exists("searchQuery", $requestData) ? filter_var($requestData["searchQuery"], FILTER_SANITIZE_STRING) : null;
        $searchQuery = empty($search) || $search === "null" ? "%" : "%" . $search . "%";
        $sort = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortColumn = empty($sort) || $sort === "null" ? null : $sort;
        $sortDirection = array_key_exists("sortDirection", $requestData) ? filter_var($requestData["sortDirection"], FILTER_SANITIZE_STRING) : null;

        $recordsTotal = $this->getMapper()->tableCount($account->id);
        $recordsFiltered = $this->getMapper()->tableCount($account->id, $searchQuery);

        $data = $this->getMapper()->getTableData($account->id, $sortColumn, $sortDirection, $length, $start, $searchQuery);
        $table = $this->renderTableRows($account, $data);

        $response_data = [
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $table
        ];

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    private function renderTableRows($account, array $table) {
        $rendered_data = [];
        foreach ($table as $dataset) {
            $row = [];
            $row[] = '<span class="confirm_transaction ' . ($dataset["is_confirmed"] > 0 ? 'is_confirmed' : '') . '" data-id="' . $dataset["id"] . '"><span class="check-circle">' . Utility::getFontAwesomeIcon('far fa-check-circle') . '</span><span class="cross-circle">' . Utility::getFontAwesomeIcon('far fa-times-circle') . '</span></span>';
            $row[] = $dataset["date"];
            $row[] = $dataset["time"];
            $row[] = $dataset["description"];
            $row[] = $dataset["value"];
            $row[] = $dataset["acc_from"];
            $row[] = $dataset["acc_to"];
            $row[] = is_null($dataset["finance_entry"]) && is_null($dataset["bill_entry"]) ? '<a href="' . $this->router->urlFor('finances_transaction_edit', ['id' => $dataset["id"]]) . '?account='.$account->getHash().'">'.Utility::getFontAwesomeIcon('fas fa-edit').'</a>': '';
            $row[] = is_null($dataset["finance_entry"]) && is_null($dataset["bill_entry"]) ? '<a href="#" data-url="' . $this->router->urlFor('finances_transaction_delete', ['id' => $dataset["id"]]) . '" class="btn-delete">'.Utility::getFontAwesomeIcon('fas fa-trash').'</span></a>' : '';

            $rendered_data[] = ["data" => $row, "attributes" => ["data-confirmed" => $dataset["is_confirmed"]]];
        }
        return $rendered_data;
    }

    public function edit($entry_id, $account_hash = null) {
        $entry = $this->getEntry($entry_id);

        if(!is_null($entry) && (!is_null($entry->finance_entry) || !is_null($entry->bill_entry))){
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $accounts = $this->account_service->getAllAccountsOrderedByName();

        try{
            $account = $this->account_service->getFromHash($account_hash);
        }catch(\Exception $e){
            $account = null;
        }

        return new Payload(Payload::$RESULT_HTML, [
            'entry' => $entry,
            'accounts' => $accounts,
            'account' => $account
        ]);
    }


    public function view($entry_id) {
        $entry = $this->getEntry($entry_id);

        $accounts = $this->account_service->getAllAccountsOrderedByName();

        return new Payload(Payload::$RESULT_HTML, [
            'entry' => $entry,
            'accounts' => $accounts
        ]);
    }

    public function setConfirmed($data) {
        $response_data = ['status' => 'error'];

        if (array_key_exists("state", $data) && in_array($data["state"], array(0, 1)) && array_key_exists("transaction", $data)) {

            $transaction = intval($data["transaction"]);
            $state = intval($data["state"]);

            $this->mapper->set_confirmed($transaction, $state);

            $response_data = ['status' => 'success'];
        }
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

}
