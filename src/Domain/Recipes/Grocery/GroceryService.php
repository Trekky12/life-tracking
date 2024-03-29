<?php

namespace App\Domain\Recipes\Grocery;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;

class GroceryService extends Service
{

    private $translation;

    public function __construct(LoggerInterface $logger, CurrentUser $user, GroceryMapper $mapper, Translator $translation)
    {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->translation = $translation;
    }

    public function index()
    {
        $groceries = $this->mapper->getAll();
        return new Payload(Payload::$RESULT_HTML, ['groceries' => $groceries]);
    }

    public function edit($entry_id)
    {
        if ($this->isOwner($entry_id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry]);
    }

    public function getIngredients($selected = null)
    {
        $all_groceries = $this->mapper->getAll();

        $groceries = [];

        foreach ($all_groceries as $grocery) {
            if ($grocery->is_food == 0) {
                continue;
            }
            $label = $grocery->name;

            if ($grocery->unit) {
                $label .= " (" . $grocery->unit . ")";
            }

            $el = ["text" => $label, "value" => $grocery->id];

            $groceries[] = $el;
        }

        return new Payload(Payload::$RESULT_JSON, $groceries);
    }

    public function getGroceries($data)
    {
        $response_data = ["data" => [], "status" => "success"];

        $query = array_key_exists('query', $data) ? filter_var($data['query'], FILTER_SANITIZE_STRING) : "";
        $is_food = array_key_exists('food', $data) ? intval(filter_var($data['food'], FILTER_SANITIZE_NUMBER_INT)) > 0 : null;

        $all_groceries = $this->mapper->getGroceriesFromInput($query, $is_food);

        $groceries = [];

        foreach ($all_groceries as $grocery) {
            $label = $grocery->name;

            if ($grocery->unit) {
                $label .= " (" . $grocery->unit . ")";
            }

            $el = ["text" => $label, "id" => $grocery->id, "name" => $grocery->name, "unit" => $grocery->unit];

            $groceries[] = $el;
        }

        $response_data["data"] = $groceries;

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function getGroceryByName($grocery_input){
        return $this->mapper->getGroceryByName($grocery_input);
    }

    
}
