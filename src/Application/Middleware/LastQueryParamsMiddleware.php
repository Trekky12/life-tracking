<?php

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Slim\Http\ServerRequest as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\SessionUtility;

class LastQueryParamsMiddleware {

    protected $current_user;

    public function __construct(CurrentUser $current_user) {
        $this->current_user = $current_user;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {
        $routeContext = RouteContext::fromRequest($request);
        $baseRoute = $routeContext->getRoute();
        $routeName = $baseRoute->getName();
        $params = $request->getQueryParams();
        
        // get last saved urls
        $lastUrls = SessionUtility::getSessionVar("lastURLS", []);
        // save new params for this route
        $lastUrls[$routeName] = $params;
        // only save 5 entries
        if (count($lastUrls) > 5) {
            array_shift($lastUrls);
        }
        SessionUtility::setSessionVar("lastURLS", $lastUrls);

        return $handler->handle($request);
    }

}
