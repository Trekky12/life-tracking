<?php

namespace App\Finances\Assignment;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Finances\Category\CategoryService;

class Controller extends \App\Base\Controller {

    private $cat_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            AssignmentService $service,
            CategoryService $cat_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->cat_service = $cat_service;
    }

    public function edit(Request $request, Response $response) {
        $entry_id = $request->getAttribute('id');
        $entry = $this->service->getEntry($entry_id);
        $categories = $this->cat_service->getAllCategoriesOrderedByName();
        return $this->twig->render($response, 'finances/assignment/edit.twig', ['entry' => $entry, 'categories' => $categories]);
    }

    public function index(Request $request, Response $response) {
        $assignments = $this->service->getAllAssignmentsOrderedByDescription();
        $categories = $this->cat_service->getAllCategoriesOrderedByName();
        return $this->twig->render($response, 'finances/assignment/index.twig', ['assignments' => $assignments, 'categories' => $categories]);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        $new_id = $this->doSave($id, $data, null);

        $redirect_url = $this->router->urlFor('finances_categories_assignment');
        return $response->withRedirect($redirect_url, 301);
    }
    
    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $response_data = $this->doDelete($id);
        return $response->withJson($response_data);
    }

}
