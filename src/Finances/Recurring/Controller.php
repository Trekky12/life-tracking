<?php

namespace App\Finances\Recurring;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;

class Controller extends \App\Base\Controller {

    private $cat_service;
    private $paymethod_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            RecurringService $service,
            \App\Finances\Category\CategoryService $cat_service,
            \App\Finances\Paymethod\PaymethodService $paymethod_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->cat_service = $cat_service;
        $this->paymethod_service = $paymethod_service;
    }

    public function index(Request $request, Response $response) {
        $list = $this->service->getAllRecurring();
        $categories = $this->cat_service->getAllCategoriesOrderedByName();
        return $this->twig->render($response, 'finances/recurring/index.twig', ['list' => $list, 'categories' => $categories, 'units' => FinancesEntryRecurring::getUnits()]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = $this->service->getEntry($entry_id);

        $categories = $this->cat_service->getAllCategoriesOrderedByName();
        $paymethods = $this->paymethod_service->getAllPaymethodsOrderedByName();

        return $this->twig->render($response, 'finances/recurring/edit.twig', ['entry' => $entry, 'categories' => $categories, 'paymethods' => $paymethods, 'units' => FinancesEntryRecurring::getUnits()]);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        $new_id = $this->doSave($id, $data, null);

        $this->service->setLastRun($new_id);

        $redirect_url = $this->router->urlFor('finances_recurring');
        return $response->withRedirect($redirect_url, 301);
    }

    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $response_data = $this->doDelete($id);
        return $response->withJson($response_data);
    }

}
