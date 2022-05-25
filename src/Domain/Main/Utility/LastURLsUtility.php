<?php

namespace App\Domain\Main\Utility;

class LastURLsUtility {

    public static function setLastURLForRoute($routeName, $queryParams, $routeArguments = []) {
        $identifier = $routeName. "|".http_build_query($routeArguments);

        // get last saved urls
        $lastUrls = LastURLsUtility::getLastURLs();
        
        // save new params for this route
        $lastUrls[$identifier] = $queryParams;
        // only save 5 entries
        if (count($lastUrls) > 10) {
            array_shift($lastUrls);
        }
        $_SESSION["lastURLS"] = $lastUrls;
    }

    public static function getLastURLs() {
        if(!array_key_exists("lastURLS", $_SESSION)){
            return [];
        }
        return $_SESSION["lastURLS"];
    }

    public static function getLastURLsForRoute($routeName, $routeArguments){
        $queryParams = [];
        
        $lastUrls = LastURLsUtility::getLastURLs();

        $identifier = $routeName. "|".http_build_query($routeArguments);
        if (array_key_exists($identifier, $lastUrls)) {
            $queryParams = $lastUrls[$identifier];
        }

        return $queryParams;
    }

}
