<?php

namespace App\Application\TwigExtensions;

use App\Domain\Main\Utility\LastURLsUtility;
use App\Domain\Main\Utility\SessionUtility;
use Slim\Interfaces\RouteParserInterface;


class LastQueryParamsExtension extends \Twig\Extension\AbstractExtension
{

    protected $routeParser;

    public function __construct(RouteParserInterface $routeParser)
    {
        $this->routeParser = $routeParser;
    }

    public function getName()
    {
        return 'slim-twig-last-urls';
    }

    /**
     * Callback for twig.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('url_for_with_last_query_params', [$this, 'urlForWithLastQueryParams']),
        ];
    }

    public function urlForWithLastQueryParams(string $routeName, array $data = []): string
    {
        $queryParams = LastURLsUtility::getLastURLsForRoute($routeName, $data);
        return $this->routeParser->urlFor($routeName, $data, $queryParams);
    }
}
