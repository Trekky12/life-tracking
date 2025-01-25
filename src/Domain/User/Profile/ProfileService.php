<?php

namespace App\Domain\User\Profile;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use Intervention\Image\ImageManager;
use App\Application\Payload\Payload;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Algorithm;
use RobThree\Auth\Providers\Qr\EndroidQrCodeWithLogoProvider;
use App\Domain\Main\Utility\SessionUtility;
use App\Domain\Main\Utility\Utility;

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
            $manager = new ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver()
            );
            $img = $manager->read($complete_file_name . '.' . $file_extension);
            /**
             * @link http://image.intervention.io/api/resize
             */
            /* $img->resize( 100, null, function ($constraint) {
              $constraint->aspectRatio();
              } ); */
            $img->resize(100, 100, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->crop(100, 100);
            $img->save($complete_file_name . '-small.' . $file_extension);

            $img->resize(50, 50, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->crop(50, 50);
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
        $old_password = array_key_exists('oldpassword', $data) ? Utility::filter_string_polyfill($data['oldpassword']) : null;
        $new_password1 = array_key_exists('newpassword1', $data) ? Utility::filter_string_polyfill($data['newpassword1']) : null;
        $new_password2 = array_key_exists('newpassword2', $data) ? Utility::filter_string_polyfill($data['newpassword2']) : null;

        if (!$this->user_service->comparePasswords($old_password, $new_password1, $new_password2)) {
            $this->logger->warning("Update Password Failed, Passwords missmatch");
            return new Payload(Payload::$STATUS_PASSWORD_MISSMATCH);
        }


        /**
         * Verify old password
         */
        if (!$this->user_service->verifyPassword($old_password)) {
            $this->logger->warning("Update Password Failed, Old Password Wrong");
            return new Payload(Payload::$STATUS_PASSWORD_WRONG);
        }

        /**
         * Update Password
         */
        $this->user_service->updatePassword($new_password1);
        $this->logger->info("Update Password Success");
        return new Payload(Payload::$STATUS_PASSWORD_SUCCESS);
    }

    public function editProfilePage(): Payload {
        return new Payload(Payload::$RESULT_HTML);
    }

    public function updateUser($data) {
        if ($this->user_service->updateUser($data)) {
            $this->logger->notice("Update Profile");
            return new Payload(Payload::$STATUS_UPDATE);
        } else {
            $this->logger->notice("No Update of Profile", array("id" => $data));
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
            $this->logger->notice("Remove profile image");
            return new Payload(Payload::$STATUS_PROFILE_IMAGE_DELETED);
        }

        /**
         * Update Image
         */
        if (!array_key_exists('image', $files) || empty($files['image'])) {
            $this->logger->error("Update Profile Image, Image Error", array("files" => $files));
            return new Payload(Payload::$STATUS_PROFILE_IMAGE_ERROR);
        }

        $image = $files['image'];

        $upload = $this->saveImage($image);

        if ($upload) {
            $this->logger->notice("Update Profile Image, Image Set", array("image" => $image->getClientFilename()));
            return new Payload(Payload::$STATUS_PROFILE_IMAGE_SET);
        } else {
            $this->logger->error("Update Profile Image, Image Error", array("files" => $files));
            return new Payload(Payload::$STATUS_PROFILE_IMAGE_ERROR);
        }
    }

    public function twoFactorAuthPage(): Payload {

        $hasSecret = $this->current_user->getUser()->secret;

        $response_data = [
            'error' => false,
            'hasSecret' => $hasSecret
        ];

        if (!$hasSecret) {
            $tfa = new TwoFactorAuth(new EndroidQrCodeWithLogoProvider(), null, 6, 30, Algorithm::Sha1);
            $secret = $tfa->createSecret(160);

            $response_data['secret'] = chunk_split($secret, 4, " ");
            $response_data['qr'] = $tfa->getQRCodeImageAsDataUri($this->current_user->getUser()->login . "@life-tracking", $secret);

            /* try {
              $tfa->ensureCorrectTime();
              } catch (RobThree\Auth\TwoFactorAuthException $ex) {
              $this->logger->error("2FA not working, host time is off", []);
              $response_data['error'] = true;
              } */

            SessionUtility::setSessionVar("secret", $secret);
        }

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function saveTwoFactorAuth($data) {
        $code = array_key_exists("code", $data) ? filter_var($data["code"], FILTER_SANITIZE_NUMBER_INT) : null;
        $secret = SessionUtility::getSessionVar("secret");
        SessionUtility::deleteSessionVar("secret");

        $tfa = new TwoFactorAuth(new EndroidQrCodeWithLogoProvider());
        $result = $tfa->verifyCode($secret, $code);

        if ($result) {
            $this->logger->notice("Save 2FA secret", []);
            $this->user_service->setTwoFactorAuthSecret($secret);
            return new Payload(Payload::$STATUS_TWOFACTOR_SUCCESS);
        }
        $this->logger->error("2FA secret was wrong!", []);
        return new Payload(Payload::$STATUS_TWOFACTOR_ERROR);
    }

    public function disableTwoFactorAuth() {
        $this->logger->notice("Disable 2FA", []);
        $this->user_service->setTwoFactorAuthSecret(null);
        return new Payload(Payload::$STATUS_TWOFACTOR_DELETE_SUCCESS);
    }
}
