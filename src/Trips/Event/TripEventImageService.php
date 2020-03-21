<?php

namespace App\Trips\Event;

use Psr\Log\LoggerInterface;
use App\Base\Settings;
use Intervention\Image\ImageManagerStatic as Image;

class TripEventImageService {

    private $logger;
    private $settings;
    private $mapper;

    public function __construct(LoggerInterface $logger,
            Settings $settings,
            Mapper $mapper) {
        $this->logger = $logger;
        $this->settings = $settings;
        $this->mapper = $mapper;
    }

    private function getFullImagePath() {
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . $this->getEventImagePath();
    }

    private function getEventImagePath() {
        return $this->settings->getAppSettings()['upload_folder'] . '/events/';
    }

    public function deleteImage($event_id) {
        $event = $this->mapper->get($event_id);

        $folder = $this->getFullImagePath();

        $thumbnail = $event->get_thumbnail('small');
        $image = $event->get_image();
        unlink($folder . "/" . $thumbnail);
        unlink($folder . "/" . $image);

        $this->mapper->update_image($event->id, null);
    }

    public function saveImage($event_id, $image) {
        $event = $this->mapper->get($event_id);

        $folder = $this->getFullImagePath();

        if ($image->getError() === UPLOAD_ERR_OK) {

            $uploadFileName = $image->getClientFilename();
            $file_extension = pathinfo($uploadFileName, PATHINFO_EXTENSION);
            $file_wo_extension = pathinfo($uploadFileName, PATHINFO_FILENAME);
            $file_name = hash('sha256', time() . rand(0, 1000000) . $event->id) . '_' . $file_wo_extension;
            $complete_file_name = $folder . DIRECTORY_SEPARATOR . $file_name;

            $image->moveTo($complete_file_name . '.' . $file_extension);
            /**
             * Create Thumbnail
             */
            $img = Image::make($complete_file_name . '.' . $file_extension);
            /**
             * @link http://image.intervention.io/api/resize
             */
            $img->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($complete_file_name . '-small.' . $file_extension);

            $this->mapper->update_image($event->id, $file_name . '.' . $file_extension);

            return "/" . $this->getEventImagePath() . $file_name . '-small.' . $file_extension;
        }

        return false;
    }

}
