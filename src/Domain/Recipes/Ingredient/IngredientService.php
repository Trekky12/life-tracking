<?php

namespace App\Domain\Recipes\Ingredient;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;

class IngredientService extends Service {

    private $translation;

    public function __construct(LoggerInterface $logger, CurrentUser $user, IngredientMapper $mapper, Translator $translation) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->translation = $translation;
    }

    public function index() {
        $ingredients = $this->mapper->getAll();
        return new Payload(Payload::$RESULT_HTML, ['ingredients' => $ingredients]);
    }

    public function edit($entry_id) {
        if ($this->isOwner($entry_id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry]);
    }

    public function getIngredients($selected = null) {
        $all_ingredients = $this->mapper->getAll();

        $ingredients = [];
        
        foreach ($all_ingredients as $ingredient) {
            $label = $ingredient->name;

            if ($ingredient->unit) {
                $label .= " (" . $ingredient->unit . ")";
            }

            $el = ["label" => $label, "value" => $ingredient->id];

            $ingredients[] = $el;
        }

        return new Payload(Payload::$RESULT_JSON, $ingredients);
    }

}
