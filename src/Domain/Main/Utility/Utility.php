<?php

namespace App\Domain\Main\Utility;

class Utility {

    public static function getIP() {
        return filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
    }

    public static function getURI() {
        return filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING);
    }

    public static function getAgent() {
        return filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING);
    }

    public static function getRequestURI(\Psr\Http\Message\RequestInterface $request) {
        $requestURI = $request->getUri();
        $path = $requestURI->getPath();
        $params = $requestURI->getQuery();
        $uri = strlen($params) > 0 ? $path . '?' . $params : $path;
        return $uri;
    }

    /**
     * @see https://stackoverflow.com/a/834355
     */
    public static function startsWith($haystack, $needle) {
        return (substr($haystack, 0, strlen($needle)) === $needle);
    }

}
