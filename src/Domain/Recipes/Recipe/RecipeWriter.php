<?php

namespace App\Domain\Recipes\Recipe;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use Intervention\Image\ImageManagerStatic as Image;

class RecipeWriter extends ObjectActivityWriter {

    private $service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, RecipeMapper $mapper, RecipeService $service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->service = $service;
    }

    public function save($id, $data, $additionalData = null): Payload {

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
                $img = Image::make($complete_file_name . '.' . $file_extension);
                $img->resize(400, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
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

    public function getObjectViewRoute(): string {
        return 'recipes_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "recipes";
    }

    private function getStepData($step_data, $idx) {
        $name = array_key_exists("name", $step_data) && !empty($step_data["name"]) ? filter_var($step_data["name"], FILTER_SANITIZE_STRING) : null;
        $description = array_key_exists("description", $step_data) && !empty($step_data["description"]) ? filter_var($step_data["description"], FILTER_SANITIZE_STRING) : null;
        $preparation_time = array_key_exists("preparation_time", $step_data) && !empty($step_data["preparation_time"]) ? intval(filter_var($step_data["preparation_time"], FILTER_SANITIZE_NUMBER_INT)) : null;
        $waiting_time = array_key_exists("waiting_time", $step_data) && !empty($step_data["waiting_time"]) ? intval(filter_var($step_data["waiting_time"], FILTER_SANITIZE_NUMBER_INT)) : null;

        return ["position" => $idx, "name" => $name, "description" => $description, "preparation_time" => $preparation_time, "waiting_time" => $waiting_time];
    }

    private function getStepIngredients($step_data) {

        $ingredients = [];

        if (array_key_exists("ingredients", $step_data) && is_array($step_data["ingredients"])) {
            foreach ($step_data["ingredients"] as $idx => $ingredient_data) {

                $ingredient = array_key_exists("ingredient", $ingredient_data) && !empty($ingredient_data["ingredient"]) ? intval(filter_var($ingredient_data["ingredient"], FILTER_SANITIZE_NUMBER_INT)) : null;
                $amount = array_key_exists("amount", $ingredient_data) && !empty($ingredient_data["amount"]) ? filter_var($ingredient_data["amount"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
                $notice = array_key_exists("notice", $ingredient_data) && !empty($ingredient_data["notice"]) ? filter_var($ingredient_data["notice"], FILTER_SANITIZE_STRING) : null;

                $ingredients[] = ["position" => $idx, "ingredient" => $ingredient, "amount" => $amount, "notice" => $notice];
            }
        }

        return $ingredients;
    }

}
