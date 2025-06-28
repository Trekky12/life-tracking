<?php

namespace App\Domain\Workouts\Muscle;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Base\Settings;
use Intervention\Image\ImageManager;
use App\Domain\Settings\SettingsMapper;

class MuscleService extends Service {

    private $settings;
    private $settings_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, MuscleMapper $mapper, Settings $settings, SettingsMapper $settings_mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->settings = $settings;
        $this->settings_mapper = $settings_mapper;
    }

    public function index() {
        $muscles = $this->mapper->getAll();
        return new Payload(Payload::$RESULT_HTML, ['muscles' => $muscles]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry]);
    }

    public function deleteImage($muscle_id, $primary = true) {
        try {
            $exercise = $this->mapper->get($muscle_id);

            $folder = $this->getFullImagePath();

            if ($primary) {
                $image = $exercise->get_image_primary();
                unlink($folder . "/" . $image);
            } else {
                $image = $exercise->get_image_secondary();
                unlink($folder . "/" . $image);
            }

            $this->logger->notice("Delete Muscle Image", array("id" => $muscle_id));
        } catch (\Exception $e) {
            $this->logger->notice("Delete Muscle Image Error", array("id" => $muscle_id, 'error' => $e->getMessage()));
        }
    }

    public function getFullImagePath() {
        return dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . $this->getImagePath();
    }

    private function getImagePath() {
        return $this->settings->getAppSettings()['upload_folder'] . '/muscles/';
    }

    public function editMuscleBaseImagePage(): Payload {
        $baseMuscleImage = $this->settings_mapper->getSetting('basemuscle_image');
        return new Payload(Payload::$RESULT_HTML, ['image' => $baseMuscleImage]);
    }

    public function updateBaseImage($data, $files): Payload {
        /**
         * Delete Image
         */
        $delete = array_key_exists("delete_image", $data) ? intval(filter_var($data["delete_image"], FILTER_SANITIZE_NUMBER_INT)) == 1 : false;
        if ($delete) {
            $this->deleteBaseImage();
            $this->logger->notice("Remove base muscle image");
            return new Payload(Payload::$STATUS_DELETE_SUCCESS);
        }

        /**
         * Update Image
         */
        if (!array_key_exists('image', $files) || empty($files['image'])) {
            $this->logger->error("Update Base Muscle Image, Image Error", array("files" => $files));
            return new Payload(Payload::$STATUS_ERROR);
        }

        $image = $files['image'];

        $upload = $this->saveBaseImage($image);

        if ($upload) {
            $this->logger->notice("Update Base Muscle Image, Image Set", array("image" => $image->getClientFilename()));
            return new Payload(Payload::$STATUS_NEW);
        } else {
            $this->logger->error("Update Base Muscle Image, Image Error", array("files" => $files));
            return new Payload(Payload::$STATUS_ERROR);
        }
    }

    public function deleteBaseImage() {
        $user = $this->current_user->getUser();
        $folder = $this->getFullImagePath();

        $image = $user->get_image();
        unlink($folder . "/" . $image);

        $this->settings_mapper->updateSetting('basemuscle_image', null);
    }

    public function saveBaseImage($image) {
        $user = $this->current_user->getUser();
        $folder = $this->getFullImagePath();

        if ($image->getError() === UPLOAD_ERR_OK) {

            $uploadFileName = $image->getClientFilename();
            $file_extension = pathinfo($uploadFileName, PATHINFO_EXTENSION);
            $file_wo_extension = pathinfo($uploadFileName, PATHINFO_FILENAME);
            $file_name = hash('sha256', time() . rand(0, 1000000) . $user->id) . '_' . $file_wo_extension;
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

            $this->settings_mapper->addOrUpdateSetting('basemuscle_image',  $file_name . '.' . $file_extension, "String");

            return true;
        }

        return false;
    }

}
