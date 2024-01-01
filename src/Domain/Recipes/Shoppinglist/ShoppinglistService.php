<?php

namespace App\Domain\Recipes\Shoppinglist;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Application\Payload\Payload;
use App\Domain\Recipes\Grocery\GroceryService;
use App\Domain\Recipes\Grocery\GroceryWriter;

class ShoppinglistService extends Service {

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

    public function index() {
        $shoppinglists = $this->getShoppingListsOfUser();

        return new Payload(Payload::$RESULT_HTML, ['shoppinglists' => $shoppinglists]);
    }

    public function edit($entry_id) {
        if ($this->isOwner($entry_id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        $users = $this->user_service->getAll();

        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'users' => $users]);
    }

    public function view($hash) {

        $shoppinglist = $this->getFromHash($hash);

        if (!$this->isMember($shoppinglist->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        return new Payload(Payload::$RESULT_HTML, [
            'isShoppingList' => true,
            'shoppinglist' => $shoppinglist
        ]);
    }

    public function getShoppingListsOfUser() {
        return $this->mapper->getUserItems('t.createdOn DESC, name');
    }
}
