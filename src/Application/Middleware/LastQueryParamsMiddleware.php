<?php

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use App\Domain\Main\Utility\LastURLsUtility;
use Slim\Routing\RouteResolver;
use Slim\Routing\RouteCollector;

class LastQueryParamsMiddleware
{

    protected $current_user;
    private $resolver;
    private $collector;

    public function __construct(RouteResolver $resolver, RouteCollector $collector)
    {
        $this->resolver = $resolver;
        $this->collector = $collector;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $routeName = $route->getName();
        $routeArguments = $route->getArguments();

        $params = $request->getQueryParams();
        if ($route->getName() === "store_query_params") {
            $data = $request->getParsedBody();
            $path = array_key_exists('path', $data) ? filter_var($data['path'], FILTER_SANITIZE_STRING) : null;
            $query_params = array_key_exists('params', $data) ? filter_var($data['params'], FILTER_SANITIZE_STRING) : null;

            if (!is_null($path)) {
                $res = $this->resolver->computeRoutingResults($path, "GET");
                $route = $this->collector->lookupRoute($res->getRouteIdentifier());
                parse_str(ltrim($query_params, "?"), $params);

                $routeName = $route->getName();
                $routeArguments = $res->getRouteArguments();
            }
        }
        
        LastURLsUtility::setLastURLForRoute($routeName, $params, $routeArguments);

        return $handler->handle($request);
    }
}
