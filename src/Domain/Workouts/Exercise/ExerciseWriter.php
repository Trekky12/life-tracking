<?php

namespace App\Domain\Workouts\Exercise;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class ExerciseWriter extends ObjectActivityWriter {

    private $service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, ExerciseMapper $mapper, ExerciseService $service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;

        $this->service = $service;
    }

    public function save($id, $data, $additionalData = null): Payload {

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

                $this->logger->notice("Upload Exercise Image, Image Set", array("id" => $id, "image" => $image->getClientFilename()));

                $data["set_image"] = $file_name . '.' . $file_extension;
            }
        }
        if (array_key_exists('thumbnail', $files) && !empty($files['thumbnail'])) {
            $image = $files['thumbnail'];

            if ($image->getError() === UPLOAD_ERR_OK) {
                $folder = $this->service->getFullImagePath();

                $uploadFileName = $image->getClientFilename();
                $file_extension = pathinfo($uploadFileName, PATHINFO_EXTENSION);
                $file_wo_extension = pathinfo($uploadFileName, PATHINFO_FILENAME);
                $file_name = hash('sha256', time() . rand(0, 1000000) . $id) . '_' . $file_wo_extension . '-thumbnail';
                $complete_file_name = $folder . DIRECTORY_SEPARATOR . $file_name;

                $image->moveTo($complete_file_name . '.' . $file_extension);

                $this->logger->notice("Upload Exercise Thumbnail Image, Image Set", array("id" => $id, "image" => $image->getClientFilename()));

                $data["set_thumbnail"] = $file_name . '.' . $file_extension;
            }
        }

        /**
         * Delete Image
         */
        $delete = array_key_exists("delete_image", $data) ? intval(filter_var($data["delete_image"], FILTER_SANITIZE_NUMBER_INT)) == 1 : false;
        if ($id && $delete) {
            $this->service->deleteImage($id);
            $this->logger->notice("Remove exercise image", array("id" => $id));

            $data['delete_image'] = true;
        }

        $delete_thumbnail = array_key_exists("delete_thumbnail", $data) ? intval(filter_var($data["delete_thumbnail"], FILTER_SANITIZE_NUMBER_INT)) == 1 : false;
        if ($id && $delete_thumbnail) {
            $this->service->deleteImage($id, true);
            $this->logger->notice("Remove exercise thumbnail image", array("id" => $id));

            $data['delete_thumbnail'] = true;
        }

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        if ($payload->getStatus() != Payload::$STATUS_ERROR || $payload->getStatus() == Payload::$STATUS_PARSING_ERRORS) {

            /**
             * Primary muscles groups
             */
            $this->mapper->deleteMusclesGroups($id, true);
            if (array_key_exists("muscle_groups_primary", $data) && is_array($data["muscle_groups_primary"]) && !empty($data["muscle_groups_primary"])) {
                $muscle_groups_primary = filter_var_array($data["muscle_groups_primary"], FILTER_SANITIZE_NUMBER_INT);

                $this->mapper->addMuscleGroups($entry->id, $muscle_groups_primary, true);
            }
            /**
             * Secondary muscles groups
             */
            $this->mapper->deleteMusclesGroups($id, false);
            if (array_key_exists("muscle_groups_secondary", $data) && is_array($data["muscle_groups_secondary"]) && !empty($data["muscle_groups_secondary"])) {
                $muscle_groups_secondary = filter_var_array($data["muscle_groups_secondary"], FILTER_SANITIZE_NUMBER_INT);

                $this->mapper->addMuscleGroups($entry->id, $muscle_groups_secondary, false);
            }
        }

        return $payload;
    }

    public function getObjectViewRoute(): string {
        return 'workouts_exercises_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "workouts_exercises";
    }

}
