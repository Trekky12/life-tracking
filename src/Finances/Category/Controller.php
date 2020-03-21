<?php

namespace App\Finances\Category;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;

class Controller extends \App\Base\Controller {

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            CategoryService $service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
    }

    public function index(Request $request, Response $response) {
        $categories = $this->service->getAllCategoriesOrderedByName();
        return $this->twig->render($response, 'finances/category/index.twig', ['categories' => $categories]);
    }

    public function edit(Request $request, Response $response) {
        $entry_id = $request->getAttribute('id');
        $entry = $this->service->getEntry($entry_id);
        return $this->twig->render($response, 'finances/category/edit.twig', ['entry' => $entry]);
    }
    
    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        
        $new_id = $this->doSave($id, $data, null);
        
        $this->service->setDefaultCategoryWhenNotSet($new_id);

        $redirect_url = $this->router->urlFor('finances_categories');
        return $response->withRedirect($redirect_url, 301);
    }
    
    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $response_data = $this->doDelete($id);
        return $response->withJson($response_data);
    }

}
