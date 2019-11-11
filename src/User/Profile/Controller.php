<?php

namespace App\User\Profile;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Intervention\Image\ImageManagerStatic as Image;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\User\User';
        $this->index_route = 'users';

        $this->mapper = new \App\User\Mapper($this->ci);
        $this->token_mapper = new \App\User\Token\Mapper($this->ci);
        
        $this->logger = $this->ci->get('logger');
    }

    public function changePassword(Request $request, Response $response) {

        $user = $this->ci->get('helper')->getUser();

        if ($request->isPost()) {

            $data = $request->getParsedBody();
            $old_password = array_key_exists('oldpassword', $data) ? filter_var($data['oldpassword'], FILTER_SANITIZE_STRING) : null;
            $new_password1 = array_key_exists('newpassword1', $data) ? filter_var($data['newpassword1'], FILTER_SANITIZE_STRING) : null;
            $new_password2 = array_key_exists('newpassword2', $data) ? filter_var($data['newpassword2'], FILTER_SANITIZE_STRING) : null;

            if (empty($old_password) || empty($new_password1) || empty($new_password2) || $new_password1 !== $new_password2) {
                $this->ci->get('flash')->addMessageNow('message', $this->ci->get('helper')->getTranslatedString("PASSWORD1AND2MUSTMATCH"));
                $this->ci->get('flash')->addMessageNow('message_type', 'danger');
                
                $this->logger->addWarning("Update Passord Success, Passwords missmatch");

                return $this->ci->view->render($response, 'profile/changepw.twig', array("user" => $user));
            }


            /**
             * Verify old password
             */
            if (!password_verify($old_password, $user->password)) {
                $this->ci->get('flash')->addMessageNow('message', $this->ci->get('helper')->getTranslatedString("PASSWORD_WRONG_OLD"));
                $this->ci->get('flash')->addMessageNow('message_type', 'danger');
                
                $this->logger->addWarning("Update Passord Success, Old Password Wrong");

                return $this->ci->view->render($response, 'profile/changepw.twig', array("user" => $user));
            }

            /**
             * Update Password
             */
            $new_password_hash = password_hash($new_password1, PASSWORD_DEFAULT);
            $this->mapper->update_password($user->id, $new_password_hash);

            $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("PASSWORD_CHANGE_SUCCESS"));
            $this->ci->get('flash')->addMessage('message_type', 'success');
            
            $this->logger->addInfo("Update Passord Success");
            
            return $response->withRedirect($this->ci->get('router')->pathFor('index'), 301);
        }
        return $this->ci->view->render($response, 'profile/changepw.twig', ["user" => $user]);
    }


    public function setProfileImage(Request $request, Response $response) {


        $user = $this->ci->get('helper')->getUser();


        if ($request->isPost()) {

            /**
             * Handle uploaded file
             * @link https://akrabat.com/psr-7-file-uploads-in-slim-3/
             */
            $files = $request->getUploadedFiles();

            if (!array_key_exists('image', $files) || empty($files['image'])) {
                $this->logger->addError("Update Profile Image, Image Error", array("user" => $user->id, "files" => $files));
                
                throw new \Exception($this->ci->get('helper')->getTranslatedString("FILE_UPLOAD_ERROR"));
            }

            $image = $files['image'];


            if ($image->getError() === UPLOAD_ERR_OK) {

                $settings = $this->ci->get('settings');
                $folder = $settings['app']['upload_folder'];

                $uploadFileName = $image->getClientFilename();
                $file_extension = pathinfo($uploadFileName, PATHINFO_EXTENSION);
                $file_wo_extension = pathinfo($uploadFileName, PATHINFO_FILENAME);
                $file_name = hash('sha256', time() . rand(0, 1000000) . $user->id) . '_' . $file_wo_extension;
                $complete_file_name = $folder . '/' . $file_name;

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

                $this->mapper->update_image($user->id, $file_name . '.' . $file_extension);

                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("IMAGE_SET"));
                $this->ci->get('flash')->addMessage('message_type', 'success');
                
                $this->logger->addNotice("Update Profile Image, Image Set", array("user" => $user->id, "image" => $file_name . '.' . $file_extension));
                
            } else if ($image->getError() === UPLOAD_ERR_NO_FILE) {
                $this->mapper->update_image($user->id, null);
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("IMAGE_DELETED"));
                $this->ci->get('flash')->addMessage('message_type', 'success');
                
                $this->logger->addNotice("Update Profile Image, No File", array("user" => $user->id));
            }
            return $response->withRedirect($this->ci->get('router')->pathFor('users_profile_image'), 301);
        }
        return $this->ci->view->render($response, 'profile/image.twig', ["user" => $user]);
    }


    public function editProfile(Request $request, Response $response) {
        $user = $this->ci->get('helper')->getUser();

        if ($request->isPost()) {
            $data = $request->getParsedBody();
            
            $new_user = new \App\User\User($data);
            $elements_changed = $this->mapper->update_profile($user->id, $new_user);
            if ($elements_changed > 0) {
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_SUCCESS_UPDATE"));
                $this->ci->get('flash')->addMessage('message_type', 'success');

                $this->logger->addNotice("Update Profile", array("id" => $user->id));
            } else {
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_NOT_CHANGED"));
                $this->ci->get('flash')->addMessage('message_type', 'info');

                $this->logger->addNotice("No Update of Profile", array("id" => $user->id));
            }
            return $response->withRedirect($this->ci->get('router')->pathFor('users_profile_edit'), 301);
        }
        return $this->ci->view->render($response, 'profile/edit.twig');
    }

}
