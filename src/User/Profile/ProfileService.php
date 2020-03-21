<?php

namespace App\User\Profile;

use Psr\Log\LoggerInterface;
use App\Base\Settings;
use App\Base\CurrentUser;
use App\User\UserService;
use Intervention\Image\ImageManagerStatic as Image;

class ProfileService {

    private $logger;
    private $settings;
    private $current_user;
    private $user_service;

    public function __construct(LoggerInterface $logger,
            Settings $settings,
            CurrentUser $user,
            UserService $user_service) {
        $this->logger = $logger;
        $this->settings = $settings;
        $this->current_user = $user;
        $this->user_service = $user_service;
    }
    
    private function getFullImagePath() {
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . $this->getProfileImagePath();
    }

    private function getProfileImagePath() {
        return $this->settings->getAppSettings()['upload_folder'] . '/profile/';
    }

    public function deleteImage() {
        $user = $this->current_user->getUser();
        $folder = $this->getFullImagePath();

        $thumbnail = $user->get_thumbnail('small');
        $thumbnail2 = $user->get_thumbnail('mini');
        $image = $user->get_image();
        unlink($folder . "/" . $thumbnail);
        unlink($folder . "/" . $thumbnail2);
        unlink($folder . "/" . $image);

        $this->user_service->updateImage($user->id, null);
    }

    public function saveImage($image) {
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
            $img = Image::make($complete_file_name . '.' . $file_extension);
            /**
             * @link http://image.intervention.io/api/resize
             */
            /* $img->resize( 100, null, function ($constraint) {
              $constraint->aspectRatio();
              } ); */
            $img->fit(100, 100);
            $img->save($complete_file_name . '-small.' . $file_extension);

            $img->fit(50, 50);
            $img->save($complete_file_name . '-mini.' . $file_extension);

            $this->user_service->updateImage($user->id, $file_name . '.' . $file_extension);

            return true;
        }

        return false;
    }

}
