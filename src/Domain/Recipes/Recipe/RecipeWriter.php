<?php

namespace App\Domain\Recipes\Recipe;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use Intervention\Image\ImageManager;
use App\Domain\Recipes\Grocery\GroceryService;
use App\Domain\Recipes\Grocery\GroceryWriter;
use App\Domain\Main\Utility\Utility;

class RecipeWriter extends ObjectActivityWriter
{

    private $service;
    private $grocery_service;
    private $grocery_writer;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        RecipeMapper $mapper,
        RecipeService $service,
        GroceryService $grocery_service,
        GroceryWriter $grocery_writer
    ) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->service = $service;
        $this->grocery_service = $grocery_service;
        $this->grocery_writer = $grocery_writer;
    }

    public function save($id, $data, $additionalData = null): Payload
    {

        if ($this->service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        /**
         * Save Image
         */
        $files = array_key_exists("files", $additionalData) && is_array($additionalData["files"]) ? $additionalData["files"] : [];
        if (array_key_exists('image', $files) && !empty($files['image'])) {
            $image = $files['image'];

            if ($image->getError() === UPLOAD_ERR_OK) {
                $folder = $this->service->getFullImagePath();

                $uploadFileName = $image->getClientFilename();
                $file_extension = pathinfo($uploadFileName, PATHINFO_EXTENSION);
                $file_wo_extension = pathinfo($uploadFileName, PATHINFO_FILENAME);
                $file_name = hash('sha256', time() . rand(0, 1000000) . $id) . '_' . $file_wo_extension;
                $complete_file_name = $folder . DIRECTORY_SEPARATOR . $file_name;

                $image->moveTo($complete_file_name . '.' . $file_extension);

                /**
                 * Create Thumbnail
                 */
                $manager = new ImageManager(
                    new \Intervention\Image\Drivers\Gd\Driver()
                );
                $img = $manager->read($complete_file_name . '.' . $file_extension);
                $img = $img->scale(width: 400);
                $img->save($complete_file_name . '-small.' . $file_extension);

                $this->logger->notice("Upload Recipe Image, Image Set", array("id" => $id, "image" => $image->getClientFilename()));

                $data["set_image"] = $file_name . '.' . $file_extension;
            }
        }
        /**
         * Delete Image
         */
        $delete = array_key_exists("delete_image", $data) ? intval(filter_var($data["delete_image"], FILTER_SANITIZE_NUMBER_INT)) == 1 : false;
        if ($id && $delete) {
            $this->service->deleteImage($id);
            $this->logger->notice("Remove recipe image", array("id" => $id));

            $data['delete_image'] = true;
        }

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        $hash = $this->setHash($entry);

        // delete existing steps
        $this->mapper->deleteSteps($entry->id);
        // create new steps
        if (array_key_exists("steps", $data) && is_array($data["steps"])) {
            foreach ($data["steps"] as $idx => $step_data) {

                $step = $this->getStepData($step_data, $idx);

                $step_id = $this->mapper->addStep($entry->id, $step);

                $ingredients = $this->getStepIngredients($step_data);
                if ($ingredients) {
                    $this->mapper->addRecipeIngredients($entry->id, $step_id, $ingredients);
                }
            }
        }

        return $payload->withAdditionalData(["hash" => $hash]);
    }

    public function getObjectViewRoute(): string
    {
        return 'recipes_edit';
    }

    public function getObjectViewRouteParams($entry): array
    {
        return ["id" => $entry->id];
    }

    public function getModule(): string
    {
        return "recipes";
    }

    private function getStepData($step_data, $idx)
    {
        $name = array_key_exists("name", $step_data) && !empty($step_data["name"]) ? Utility::filter_string_polyfill($step_data["name"]) : null;
        $description = array_key_exists("description", $step_data) && !empty($step_data["description"]) ? Utility::filter_string_polyfill($step_data["description"]) : null;
        $preparation_time = array_key_exists("preparation_time", $step_data) && !empty($step_data["preparation_time"]) ? intval(filter_var($step_data["preparation_time"], FILTER_SANITIZE_NUMBER_INT)) : null;
        $waiting_time = array_key_exists("waiting_time", $step_data) && !empty($step_data["waiting_time"]) ? intval(filter_var($step_data["waiting_time"], FILTER_SANITIZE_NUMBER_INT)) : null;

        return ["position" => $idx, "name" => $name, "description" => $description, "preparation_time" => $preparation_time, "waiting_time" => $waiting_time];
    }

    private function getStepIngredients($step_data)
    {

        $ingredients = [];

        if (array_key_exists("ingredients", $step_data) && is_array($step_data["ingredients"])) {
            foreach ($step_data["ingredients"] as $idx => $ingredient_data) {

                $grocery_input = array_key_exists("ingredient", $ingredient_data) && !empty($ingredient_data["ingredient"]) ? Utility::filter_string_polyfill($ingredient_data["ingredient"]) : null;
                $amount = array_key_exists("amount", $ingredient_data) && !empty($ingredient_data["amount"]) ? filter_var($ingredient_data["amount"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
                $unit = array_key_exists("unit", $ingredient_data) && !empty($ingredient_data["unit"]) ? filter_var($ingredient_data["unit"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
                $notice = array_key_exists("notice", $ingredient_data) && !empty($ingredient_data["notice"]) ? Utility::filter_string_polyfill($ingredient_data["notice"]) : null;

                $grocery_id = array_key_exists("id", $ingredient_data) && !empty($ingredient_data["id"]) ? intval(filter_var($ingredient_data["id"], FILTER_SANITIZE_NUMBER_INT)) : null;

                $grocery = null;

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
                        $grocery_new_payload = $this->grocery_writer->save(null, ["name" => $grocery_input, "unit" => $unit, "is_food" => 1]);
                        $grocery = $grocery_new_payload->getResult();
                    }
                }

                if (!is_null($grocery)) {

                    $ingredients[] = ["position" => $idx, "ingredient" => $grocery->id, "amount" => $amount, "unit" => $unit, "notice" => $notice];

                    $this->logger->notice("Add Grocery to recipe step", array("grocery" => $grocery->id));
                }
            }
        }

        return $ingredients;
    }
}
