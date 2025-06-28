<?php

namespace App\Domain\Workouts\Muscle;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use Intervention\Image\ImageManager;

class MuscleWriter extends ObjectActivityWriter {

    private $service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, MuscleMapper $mapper, MuscleService $service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->service = $service;
    }

    public function save($id, $data, $additionalData = null): Payload {
        /**
         * Save Image
         */
        $files = array_key_exists("files", $additionalData) && is_array($additionalData["files"]) ? $additionalData["files"] : [];
        if (array_key_exists('image_primary', $files) && !empty($files['image_primary'])) {
            $image = $files['image_primary'];

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
                $img = $img->scale(width: 500);
                $img->save($complete_file_name . '-small.' . $file_extension);

                $this->logger->notice("Upload Muscle Primary Image, Image Set", array("id" => $id, "image" => $image->getClientFilename()));

                $data["set_image_primary"] = $file_name . '.' . $file_extension;
            }
        }
        if (array_key_exists('image_secondary', $files) && !empty($files['image_secondary'])) {
            $image = $files['image_secondary'];

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
                $img = $img->scale(width: 500);
                $img->save($complete_file_name . '-small.' . $file_extension);

                $this->logger->notice("Upload Muscle Secondary Image, Image Set", array("id" => $id, "image" => $image->getClientFilename()));

                $data["set_image_secondary"] = $file_name . '.' . $file_extension;
            }
        }

        /**
         * Delete Image
         */
        $delete = array_key_exists("delete_image_primary", $data) ? intval(filter_var($data["delete_image_primary"], FILTER_SANITIZE_NUMBER_INT)) == 1 : false;
        if ($id && $delete) {
            $this->service->deleteImage($id);
            $this->logger->notice("Remove primary image", array("id" => $id));

            $data['delete_image_primary'] = true;
        }

        $delete_secondary = array_key_exists("delete_image_secondary", $data) ? intval(filter_var($data["delete_image_secondary"], FILTER_SANITIZE_NUMBER_INT)) == 1 : false;
        if ($id && $delete_secondary) {
            $this->service->deleteImage($id, true);
            $this->logger->notice("Remove secondary image", array("id" => $id));

            $data['delete_image_secondary'] = true;
        }

        return parent::save($id, $data, $additionalData);
    }

    public function getObjectViewRoute(): string {
        return 'workouts_muscles_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "workouts";
    }

}
