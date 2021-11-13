<?php

namespace App\Application\TwigExtensions;

use App\Domain\Main\Utility\SessionUtility;

class LastQueryParamsExtension extends \Twig\Extension\AbstractExtension {

    public function getName() {
        return 'slim-twig-last-urls';
    }

    /**
     * Callback for twig.
     *
     * @return array
     */
    public function getFunctions() {
        return [
            new \Twig\TwigFunction('last_query_params', [$this, 'getLastQueryParams']),
        ];
    }

    public function getLastQueryParams($routeName = null) {
        $lastUrls = SessionUtility::getSessionVar("lastURLS", []);

        if (array_key_exists($routeName, $lastUrls)) {
            return http_build_query($lastUrls[$routeName]);
        }

        return "";
    }

}
