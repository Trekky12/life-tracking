<?php

namespace App\Domain\User\Profile;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use Intervention\Image\ImageManagerStatic as Image;
use App\Application\Payload\Payload;

class ProfileService extends Service {

    private $settings;
    private $user_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, Settings $settings, UserService $user_service) {
        parent::__construct($logger, $user);
        $this->settings = $settings;
        $this->user_service = $user_service;
    }

    private function getFullImagePath() {
        return dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . $this->getProfileImagePath();
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

    public function changePasswordPage(): Payload {
        return new Payload(Payload::$RESULT_HTML);
    }

    public function changePassword($data): Payload {
        $old_password = array_key_exists('oldpassword', $data) ? filter_var($data['oldpassword'], FILTER_SANITIZE_STRING) : null;
        $new_password1 = array_key_exists('newpassword1', $data) ? filter_var($data['newpassword1'], FILTER_SANITIZE_STRING) : null;
        $new_password2 = array_key_exists('newpassword2', $data) ? filter_var($data['newpassword2'], FILTER_SANITIZE_STRING) : null;

        if (!$this->user_service->comparePasswords($old_password, $new_password1, $new_password2)) {
            $this->logger->addWarning("Update Password Failed, Passwords missmatch");
            return new Payload(Payload::$STATUS_PASSWORD_MISSMATCH);
        }


        /**
         * Verify old password
         */
        if (!$this->user_service->verifyPassword($old_password)) {
            $this->logger->addWarning("Update Password Failed, Old Password Wrong");
            return new Payload(Payload::$STATUS_PASSWORD_WRONG);
        }

        /**
         * Update Password
         */
        $this->user_service->updatePassword($new_password1);
        $this->logger->addInfo("Update Password Success");
        return new Payload(Payload::$STATUS_PASSWORD_SUCCESS);
    }

    public function editProfilePage(): Payload {
        return new Payload(Payload::$RESULT_HTML);
    }

    public function updateUser($data) {
        if ($this->user_service->updateUser($data)) {
            $this->logger->addNotice("Update Profile");
            return new Payload(Payload::$STATUS_UPDATE);
        } else {
            $this->logger->addNotice("No Update of Profile", array("id" => $user->id));
            return new Payload(Payload::$STATUS_NO_UPDATE);
        }
    }

    public function editProfileImagePage(): Payload {
        return new Payload(Payload::$RESULT_HTML);
    }

    public function updateProfileImage($data, $files): Payload {
        /**
         * Delete Image
         */
        $delete = array_key_exists("delete_image", $data) ? intval(filter_var($data["delete_image"], FILTER_SANITIZE_NUMBER_INT)) == 1 : false;
        if ($delete) {
            $this->deleteImage();
            $this->logger->addNotice("Remove profile image");
            return new Payload(Payload::$STATUS_PROFILE_IMAGE_DELETED);
        }

        /**
         * Update Image
         */
        if (!array_key_exists('image', $files) || empty($files['image'])) {
            $this->logger->addError("Update Profile Image, Image Error", array("files" => $files));
            return new Payload(Payload::$STATUS_PROFILE_IMAGE_ERROR);
        }

        $image = $files['image'];

        $upload = $this->saveImage($image);

        if ($upload) {
            $this->logger->addNotice("Update Profile Image, Image Set", array("image" => $image->getClientFilename()));
            return new Payload(Payload::$STATUS_PROFILE_IMAGE_SET);
        } else {
            $this->logger->addError("Update Profile Image, Image Error", array("files" => $files));
            return new Payload(Payload::$STATUS_PROFILE_IMAGE_ERROR);
        }
    }

}
