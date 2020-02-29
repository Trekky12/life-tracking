<?php

namespace App\User\Profile;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;
use Intervention\Image\ImageManagerStatic as Image;

class Controller extends \App\Base\Controller {

    protected $model = '\App\User\User';
    protected $index_route = 'users';
    private $token_mapper;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        
        $this->token_mapper = new \App\User\Token\Mapper($this->db, $this->translation, $user);
    }

    public function changePassword(Request $request, Response $response) {

        $user = $this->user_helper->getUser();

        if ($request->isPost()) {

            $data = $request->getParsedBody();
            $old_password = array_key_exists('oldpassword', $data) ? filter_var($data['oldpassword'], FILTER_SANITIZE_STRING) : null;
            $new_password1 = array_key_exists('newpassword1', $data) ? filter_var($data['newpassword1'], FILTER_SANITIZE_STRING) : null;
            $new_password2 = array_key_exists('newpassword2', $data) ? filter_var($data['newpassword2'], FILTER_SANITIZE_STRING) : null;

            if (empty($old_password) || empty($new_password1) || empty($new_password2) || $new_password1 !== $new_password2) {
                $this->flash->addMessageNow('message', $this->translation->getTranslatedString("PASSWORD1AND2MUSTMATCH"));
                $this->flash->addMessageNow('message_type', 'danger');

                $this->logger->addWarning("Update Passord Success, Passwords missmatch");

                return $this->twig->render($response, 'profile/changepw.twig', array("user" => $user));
            }


            /**
             * Verify old password
             */
            if (!password_verify($old_password, $user->password)) {
                $this->flash->addMessageNow('message', $this->translation->getTranslatedString("PASSWORD_WRONG_OLD"));
                $this->flash->addMessageNow('message_type', 'danger');

                $this->logger->addWarning("Update Passord Success, Old Password Wrong");

                return $this->twig->render($response, 'profile/changepw.twig', array("user" => $user));
            }

            /**
             * Update Password
             */
            $new_password_hash = password_hash($new_password1, PASSWORD_DEFAULT);
            $this->user_mapper->update_password($user->id, $new_password_hash);

            $this->flash->addMessage('message', $this->translation->getTranslatedString("PASSWORD_CHANGE_SUCCESS"));
            $this->flash->addMessage('message_type', 'success');

            $this->logger->addInfo("Update Passord Success");

            return $response->withRedirect($this->router->urlFor('index'), 301);
        }
        return $this->twig->render($response, 'profile/changepw.twig', ["user" => $user]);
    }

    public function setProfileImage(Request $request, Response $response) {


        $user = $this->user_helper->getUser();


        if ($request->isPost()) {

            $folder = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . $this->settings['app']['upload_folder'];

            /**
             * Delete Image
             */
            $body = $request->getParsedBody();
            $delete = array_key_exists("delete_image", $body) ? intval(filter_var($body["delete_image"], FILTER_SANITIZE_NUMBER_INT)) == 1 : false;
            if ($delete) {
                $thumbnail = $user->get_thumbnail('small');
                $thumbnail2 = $user->get_thumbnail('mini');
                $image = $user->get_image();
                unlink($folder . "/" . $thumbnail);
                unlink($folder . "/" . $thumbnail2);
                unlink($folder . "/" . $image);

                $this->user_mapper->update_image($user->id, null);
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

                $this->user_mapper->update_image($user->id, $file_name . '.' . $file_extension);

                $this->flash->addMessage('message', $this->translation->getTranslatedString("PROFILE_IMAGE_SET"));
                $this->flash->addMessage('message_type', 'success');

                $this->logger->addNotice("Update Profile Image, Image Set", array("user" => $user->id, "image" => $file_name . '.' . $file_extension));
            } else if ($image->getError() === UPLOAD_ERR_NO_FILE) {
                $this->logger->addError("Update Profile Image, Image Error", array("user" => $user->id, "files" => $files, "error" => "No File"));

                throw new \Exception($this->translation->getTranslatedString("FILE_UPLOAD_ERROR"));
            }
            return $response->withRedirect($this->router->urlFor('users_profile_image'), 301);
        }
        return $this->twig->render($response, 'profile/image.twig', ["user" => $user]);
    }

    public function editProfile(Request $request, Response $response) {
        $user = $this->user_helper->getUser();

        if ($request->isPost()) {
            $data = $request->getParsedBody();

            $new_user = new \App\User\User($data);
            $elements_changed = $this->user_mapper->update_profile($user->id, $new_user);
            if ($elements_changed > 0) {
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
