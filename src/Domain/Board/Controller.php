<?php

namespace App\Domain\Board;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\User\UserService;
use Dflydev\FigCookies\FigRequestCookies;

class Controller extends \App\Domain\Base\Controller {

    private $user_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            BoardService $service,
            UserService $user_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->user_service = $user_service;
    }

    public function index(Request $request, Response $response) {
        $boards = $this->service->getAllOrderedByName();
        return $this->twig->render($response, 'boards/index.twig', ['boards' => $boards]);
    }

    public function edit(Request $request, Response $response) {
        $entry_id = $request->getAttribute('id');

        if ($this->service->isOwner($entry_id) === false) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $entry = $this->service->getEntry($entry_id);
        $users = $this->user_service->getAll();

        return $this->twig->render($response, 'boards/edit.twig', ['entry' => $entry, 'users' => $users]);
    }

    public function view(Request $request, Response $response) {
        $hash = $request->getAttribute('hash');

        $board = $this->service->getFromHash($hash);

        if (!$this->service->isMember($board->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $data = $this->service->view($board);

        //$sidebar_mobilevisible = filter_input(INPUT_COOKIE, 'sidebar_mobilevisible', FILTER_SANITIZE_NUMBER_INT);
        //$sidebar_desktophidden = filter_input(INPUT_COOKIE, 'sidebar_desktophidden', FILTER_SANITIZE_NUMBER_INT);
        $sidebar_mobilevisible = FigRequestCookies::get($request, 'sidebar_mobilevisible');
        $sidebar_desktophidden = FigRequestCookies::get($request, 'sidebar_desktophidden');

        $data["sidebar"] = [
            "mobilevisible" => $sidebar_mobilevisible->getValue(),
            "desktophidden" => $sidebar_desktophidden->getValue(),
        ];

        return $this->twig->render($response, 'boards/view.twig', $data);
    }

    public function setArchive(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $this->service->setArchive($data);

        $response_data = ['status' => 'success'];
        return $response->withJSON($response_data);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        $this->users_preSave = $this->service->getUsers($id);
        if ($this->service->isOwner($id) === false) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $new_id = $this->doSave($id, $data, null);

        $this->service->setHash($new_id);
        $this->service->notifyUsers($new_id);

        $redirect_url = $this->router->urlFor('boards');
        return $response->withRedirect($redirect_url, 301);
    }

    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');

        if ($this->service->isOwner($id) === false) {
            $response_data = ['is_deleted' => false, 'error' => $this->translation->getTranslatedString('NO_ACCESS')];
        } else {
            $response_data = $this->doDelete($id);
        }

        return $response->withJson($response_data);
    }

}
