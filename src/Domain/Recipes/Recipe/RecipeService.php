<?php

namespace App\Domain\Recipes\Recipe;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Base\Settings;
use App\Domain\Recipes\Cookbook\CookbookService;
use App\Domain\Main\Utility\Utility;

class RecipeService extends Service {

    private $settings;
    private $cookbook_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, RecipeMapper $mapper, Settings $settings, CookbookService $cookbook_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->settings = $settings;
        $this->cookbook_service = $cookbook_service;
    }

    public function index() {
        $recipes = $this->mapper->getAll();
        return new Payload(Payload::$RESULT_HTML, ['recipes' => $recipes]);
    }

    public function edit($entry_id) {
        if ($this->isOwner($entry_id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        $entry = $this->getEntry($entry_id);

        $steps = !is_null($entry) ? $this->mapper->getSteps($entry->id) : [];
        $ingredients = !is_null($entry) ? $this->mapper->getRecipeIngredients($entry->id) : [];

        return new Payload(Payload::$RESULT_HTML, [
            'entry' => $entry,
            'steps' => $steps,
            'ingredients' => $ingredients
        ]);
    }

    public function deleteImage($recipe_id) {
        try {
            $recipe = $this->mapper->get($recipe_id);

            $folder = $this->getFullImagePath();

            $thumbnail = $recipe->get_thumbnail('small');
            $image = $recipe->get_image();

            if (!is_null($thumbnail)) {
                unlink($folder . "/" . $thumbnail);
            }
            if (!is_null($image)) {
                unlink($folder . "/" . $image);
            }

            $this->logger->notice("Delete Recipe Image", array("id" => $recipe_id));
        } catch (\Exception $e) {
            $this->logger->notice("Delete Recipe Image Error", array("id" => $recipe_id, 'error' => $e->getMessage()));
        }
    }

    public function getFullImagePath() {
        return dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . $this->getImagePath();
    }

    private function getImagePath() {
        return $this->settings->getAppSettings()['upload_folder'] . '/recipes/';
    }

    public function view_single($hash) {
        $recipe = $this->getFromHash($hash);

        if (is_null($recipe)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $steps = $this->mapper->getSteps($recipe->id);
        $ingredients = $this->mapper->getRecipeIngredients($recipe->id);

        return new Payload(Payload::$RESULT_HTML, [
            'recipe' => $recipe,
            'steps' => $steps,
            'ingredients' => $ingredients
        ]);
    }

    public function add_to_cookbook($hash) {
        $recipe = $this->getFromHash($hash);

        if (is_null($recipe)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        return new Payload(Payload::$RESULT_HTML, [
            'recipe' => $recipe,
            'cookbooks' => $this->cookbook_service->getCookbooksOfUser()
        ]);
    }

    public function view_single_in_cookbook($cookbook_hash, $recipe_hash) {
        $recipe = $this->getFromHash($recipe_hash);

        $cookbook = $this->cookbook_service->getFromHash($cookbook_hash);

        if (is_null($recipe) || !$this->cookbook_service->isMember($cookbook->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $cookbook_recipes = $this->cookbook_service->getCookbookRecipes($cookbook->id);
        if (!in_array($recipe->id, $cookbook_recipes)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $steps = $this->mapper->getSteps($recipe->id);
        $ingredients = $this->mapper->getRecipeIngredients($recipe->id);

        return new Payload(Payload::$RESULT_HTML, [
            'recipe' => $recipe,
            'steps' => $steps,
            'ingredients' => $ingredients,
            'cookbook' => [
                'hash' => $cookbook_hash
            ]
        ]);
    }

    public function getRecipes($data) {

        $response_data = ["data" => [], "status" => "success"];
        $count = array_key_exists('count', $data) ? filter_var($data['count'], FILTER_SANITIZE_NUMBER_INT) : 20;
        $offset = array_key_exists('start', $data) ? filter_var($data['start'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $limit = sprintf("%s,%s", $offset, $count);

        $query = array_key_exists('query', $data) ? Utility::filter_string_polyfill($data['query']) : '';

        $cookbook_hash = array_key_exists('cookbook', $data) ? filter_var($data['cookbook'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        if (!is_null($cookbook_hash)) {
            $cookbook = $this->cookbook_service->getFromHash($cookbook_hash);

            if (!$this->cookbook_service->isMember($cookbook->id)) {
                return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
            }

            $response_data["data"] = $this->mapper->getRecipesFromCookbookFiltered($cookbook->id, "createdOn DESC", $limit, $query);
            $response_data["count"] = $this->mapper->getRecipesFromCookbookFilteredCount($cookbook->id, $query);
            $response_data["cookbook"] = $cookbook_hash;
        } else {
            $response_data["data"] = $this->mapper->getRecipesFiltered("createdOn DESC", $limit, $query);
            $response_data["count"] = $this->mapper->getRecipesFilteredCount($query);
            $response_data["cookbook"] = false;
        }

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

}
