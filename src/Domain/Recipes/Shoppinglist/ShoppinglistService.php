<?php

namespace App\Domain\Recipes\Shoppinglist;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Application\Payload\Payload;
use App\Domain\Recipes\Grocery\GroceryService;
use App\Domain\Recipes\Grocery\GroceryWriter;

class ShoppinglistService extends Service
{

    private $user_service;
    private $grocery_service;
    private $grocery_writer;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ShoppinglistMapper $mapper,
        UserService $user_service,
        GroceryService $grocery_service,
        GroceryWriter $grocery_writer
    ) {
        parent::__construct($logger, $user);

        $this->mapper = $mapper;
        $this->user_service = $user_service;
        $this->grocery_service = $grocery_service;
        $this->grocery_writer = $grocery_writer;
    }

    public function index()
    {
        $shoppinglists = $this->getShoppingListsOfUser();

        return new Payload(Payload::$RESULT_HTML, ['shoppinglists' => $shoppinglists]);
    }

    public function edit($entry_id)
    {
        if ($this->isOwner($entry_id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        $users = $this->user_service->getAll();

        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'users' => $users]);
    }

    public function view($hash)
    {

        $shoppinglist = $this->getFromHash($hash);

        if (!$this->isMember($shoppinglist->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        return new Payload(Payload::$RESULT_HTML, [
            'isShoppingList' => true,
            'shoppinglist' => $shoppinglist
        ]);
    }

    public function getShoppingListsOfUser()
    {
        return $this->mapper->getUserItems('t.createdOn DESC, name');
    }

    public function addEntryToShoppinglist($hash, $data)
    {
        $shoppinglist = $this->getFromHash($hash);

        if (!$this->isMember($shoppinglist->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $amount = array_key_exists("amount", $data) && !empty($data["amount"]) ? filter_var($data["amount"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $unit = array_key_exists("unit", $data) && !empty($data["unit"]) ? filter_var($data["unit"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
        $grocery_input = array_key_exists("grocery_input", $data) && !empty($data["grocery_input"]) ? trim(filter_var($data["grocery_input"], FILTER_SANITIZE_STRING)) : null;
        $grocery_id = array_key_exists("id", $data) && !empty($data["id"]) ? intval(filter_var($data["id"], FILTER_SANITIZE_NUMBER_INT)) : null;
        $notice = array_key_exists("notice", $data) && !empty($data["notice"]) ? trim(filter_var($data["notice"], FILTER_SANITIZE_STRING)) : null;

        $grocery = null;

        try {
            /**
             * Get the grocery from an optional id and compare name with the input
             */
            if (!is_null($grocery_id)) {
                $grocery = $this->grocery_service->getEntry($grocery_id);
                if (!is_null($grocery_input) && $grocery->name != $grocery_input) {
                    $grocery = null;
                }
            }

            /**
             * Get the grocery from the input field
             */
            if (is_null($grocery) && !is_null($grocery_input)) {

                $groceries = $this->grocery_service->getGroceryByName($grocery_input);

                $grocery = null;
                /**
                 * Only use this grocery if there is exactly one match, otherwise create a new grocery
                 */
                if (count($groceries) == 1) {
                    $grocery = array_pop($groceries);
                } else {
                    $grocery_new_payload = $this->grocery_writer->save(null, ["name" => $data["grocery_input"], "unit" => $data["unit"], "is_food" => 0]);
                    $grocery = $grocery_new_payload->getResult();
                }
            }

            if (!is_null($grocery)) {

                $entry_id = $this->mapper->addGrocery($shoppinglist->id, $grocery->id, $amount, $unit, $notice, 0);

                $this->logger->notice("Add Grocery to shoppinglist", array("shoppinglist" => $shoppinglist->id, "grocery" => $grocery->id));

                $payload = new Payload(Payload::$STATUS_NEW, ["id" => $entry_id]);
                return $payload->withEntry($grocery);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error creating shopping list entry", array("id" => $data, "error" => $e->getMessage()));
        }

        return new Payload(Payload::$STATUS_ERROR);
    }

    public function getShoppingListEntries($hash, $data)
    {

        $shoppinglist = $this->getFromHash($hash);

        if (!$this->isMember($shoppinglist->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $response_data = ["data" => [], "status" => "success"];
        $count = array_key_exists('count', $data) ? filter_var($data['count'], FILTER_SANITIZE_NUMBER_INT) : 20;
        $offset = array_key_exists('start', $data) ? filter_var($data['start'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $limit = sprintf("%s,%s", $offset, $count);

        $response_data["data"] = ["hash" => $hash, "entries" => $this->retrieveShoppingListEntries($shoppinglist->id, $limit)];
        $response_data["count"] = $this->mapper->getShoppingListEntriesCount($shoppinglist->id);
        return new Payload(Payload::$RESULT_HTML, $response_data);
    }


    public function setState($hash, $data)
    {
        $shoppinglist = $this->getFromHash($hash);

        if (!$this->isMember($shoppinglist->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $response_data = ['status' => 'error'];

        if (array_key_exists("state", $data) && in_array($data["state"], array(0, 1)) && array_key_exists("dataset", $data)) {

            $dataset = intval($data["dataset"]);
            $state = intval($data["state"]);

            $this->mapper->set_state($dataset, $shoppinglist->id, $state);

            $response_data = ['status' => 'success'];
        }
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function retrieveShoppingListEntries($shoppinglist_id, $limit = null, $done = null)
    {
        return $this->mapper->getShoppingListEntries($shoppinglist_id, $limit, $done);
    }

    public function deleteEntryFromShoppinglist($hash, $id)
    {
        $shoppinglist = $this->getFromHash($hash);

        if (!$this->isMember($shoppinglist->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $error = null;
        try {
            $is_deleted = $this->mapper->deleteGrocery($shoppinglist->id, $id);

            if ($is_deleted) {
                $this->logger->notice("Delete successfully " . $this->getMapper()->getDataObject(), array("id" => $id));
                return new Payload(Payload::$STATUS_DELETE_SUCCESS, $error);
            } else {
                $this->logger->error("Delete failed " . $this->getMapper()->getDataObject(), array("id" => $id));
                return new Payload(Payload::$STATUS_DELETE_ERROR, $error);
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $this->logger->error("Delete failed " . $this->getMapper()->getDataObject(), array("id" => $id, "error" => $e->getMessage()));
        }
        return new Payload(Payload::$STATUS_ERROR, $error);
    }
}
