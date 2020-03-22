<?php

namespace App\Domain\User\Profile;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;

class Controller extends \App\Domain\Base\Controller {

    private $current_user;
    private $user_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            ProfileService $service,
            CurrentUser $user,
            UserService $user_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->current_user = $user;
        $this->user_service = $user_service;
    }

    public function changePassword(Request $request, Response $response) {

        $user = $this->current_user->getUser();

        if ($request->isPost()) {

            $data = $request->getParsedBody();
            $old_password = array_key_exists('oldpassword', $data) ? filter_var($data['oldpassword'], FILTER_SANITIZE_STRING) : null;
            $new_password1 = array_key_exists('newpassword1', $data) ? filter_var($data['newpassword1'], FILTER_SANITIZE_STRING) : null;
            $new_password2 = array_key_exists('newpassword2', $data) ? filter_var($data['newpassword2'], FILTER_SANITIZE_STRING) : null;

            if (!$this->user_service->comparePasswords($old_password, $new_password1, $new_password2)) {
                $this->flash->addMessageNow('message', $this->translation->getTranslatedString("PASSWORD1AND2MUSTMATCH"));
                $this->flash->addMessageNow('message_type', 'danger');

                $this->logger->addWarning("Update Passord Success, Passwords missmatch");

                return $this->twig->render($response, 'profile/changepw.twig', array("user" => $user));
            }


            /**
             * Verify old password
             */
            if (!$this->user_service->verifyPassword($old_password)) {
                $this->flash->addMessageNow('message', $this->translation->getTranslatedString("PASSWORD_WRONG_OLD"));
                $this->flash->addMessageNow('message_type', 'danger');

                $this->logger->addWarning("Update Passord Success, Old Password Wrong");

                return $this->twig->render($response, 'profile/changepw.twig', array("user" => $user));
            }

            /**
             * Update Password
             */
            $this->user_service->updatePassword($new_password1);

            $this->flash->addMessage('message', $this->translation->getTranslatedString("PASSWORD_CHANGE_SUCCESS"));
            $this->flash->addMessage('message_type', 'success');

            $this->logger->addInfo("Update Passord Success");

            return $response->withRedirect($this->router->urlFor('index'), 301);
        }
        return $this->twig->render($response, 'profile/changepw.twig', ["user" => $user]);
    }

    public function setProfileImage(Request $request, Response $response) {

        $user = $this->current_user->getUser();

        if ($request->isPost()) {

            /**
             * Delete Image
             */
            $body = $request->getParsedBody();
            $delete = array_key_exists("delete_image", $body) ? intval(filter_var($body["delete_image"], FILTER_SANITIZE_NUMBER_INT)) == 1 : false;
            if ($delete) {

                $this->service->deleteImage();

                $this->flash->addMessage('message', $this->translation->getTranslatedString("PROFILE_IMAGE_DELETED"));
                $this->flash->addMessage('message_type', 'success');

                $this->logger->addNotice("Update Profile Image, No File", array("user" => $user->id));
                return $response->withRedirect($this->router->urlFor('users_profile_image'), 301);
            }

            /**
             * Handle uploaded file
             * @link https://akrabat.com/psr-7-file-uploads-in-slim-3/
             */
            $files = $request->getUploadedFiles();

            if (!array_key_exists('image', $files) || empty($files['image'])) {
                $this->logger->addError("Update Profile Image, Image Error", array("user" => $user->id, "files" => $files));

                throw new \Exception($this->translation->getTranslatedString("FILE_UPLOAD_ERROR"));
            }

            $image = $files['image'];

            $upload = $this->service->saveImage($image);

            if ($upload) {
                $this->flash->addMessage('message', $this->translation->getTranslatedString("PROFILE_IMAGE_SET"));
                $this->flash->addMessage('message_type', 'success');

                $this->logger->addNotice("Update Profile Image, Image Set", array("user" => $user->id, "image" => $image->getClientFilename()));
            } else {
                $this->logger->addError("Update Profile Image, Image Error", array("user" => $user->id, "files" => $files));

                throw new \Exception($this->translation->getTranslatedString("FILE_UPLOAD_ERROR"));
            }

            return $response->withRedirect($this->router->urlFor('users_profile_image'), 301);
        }
        return $this->twig->render($response, 'profile/image.twig', ["user" => $user]);
    }

    public function editProfile(Request $request, Response $response) {
        $user = $this->current_user->getUser();

        if ($request->isPost()) {
            $data = $request->getParsedBody();

            if ($this->user_service->updateUser($data)) {
                $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_SUCCESS_UPDATE"));
                $this->flash->addMessage('message_type', 'success');

                $this->logger->addNotice("Update Profile", array("id" => $user->id));
            } else {
                $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_NOT_CHANGED"));
                $this->flash->addMessage('message_type', 'info');

                $this->logger->addNotice("No Update of Profile", array("id" => $user->id));
            }
            return $response->withRedirect($this->router->urlFor('users_profile_edit'), 301);
        }
        return $this->twig->render($response, 'profile/edit.twig');
    }

}
