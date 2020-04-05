<?php

namespace App\Domain\Trips\Event;

use Psr\Log\LoggerInterface;
use App\Domain\Base\Settings;
use Intervention\Image\ImageManagerStatic as Image;
use App\Application\Payload\Payload;

class TripEventImageService {

    private $logger;
    private $settings;
    private $mapper;

    public function __construct(LoggerInterface $logger, Settings $settings, EventMapper $mapper) {
        $this->logger = $logger;
        $this->settings = $settings;
        $this->mapper = $mapper;
    }

    private function getFullImagePath() {
        return dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . $this->getEventImagePath();
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
        
        $this->logger->addNotice("Delete Event Image", array("id" => $event_id));
        
        return new Payload(Payload::$STATUS_DELETE_SUCCESS);
    }

    public function saveImage($event_id, $files) {
        $event = $this->mapper->get($event_id);

        if (!array_key_exists('image', $files) || empty($files['image'])) {
            $this->logger->addError("Update Event Image, Image Error", array("id" => $event_id, "files" => $files));
            return new Payload(Payload::$STATUS_ERROR, "No File");
        }

        $image = $files['image'];

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

            $thumbnail = "/" . $this->getEventImagePath() . $file_name . '-small.' . $file_extension;

            $this->logger->addNotice("Update Event Image, Image Set", array("id" => $event_id, "image" => $image->getClientFilename()));

            $payload = new Payload(Payload::$STATUS_UPDATE);
            return $payload->withAdditonalData(["thumbnail" => $thumbnail]);
        }

        $this->logger->addNotice("Update Event Image, No File", array("id" => $event_id));
        return new Payload(Payload::$STATUS_ERROR, "No File");
    }

}
