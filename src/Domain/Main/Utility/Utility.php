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

    /**
     * Replace textual links with real links
     * @see https://css-tricks.com/snippets/php/find-urls-in-text-make-links/
     */
    public static function replaceLinks($text) {
        $regex = "/((https?\:\/\/|(www\.))(\S+))/";

        $regexHTTP = "/((https?\:\/\/)(\S+))/";
        $replacementHTTP = '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>';

        // only www without http(s)
        $regexWWW = "/[^(https?\:\/\/)]((www\.)(\S+))/";
        $replacementWWW = '<a href="http://$1" target="_blank" rel="noopener noreferrer">$1</a>';

        $urls = [];
        if (preg_match_all($regex, $text, $urls)) {
            return preg_replace([$regexHTTP, $regexWWW], [$replacementHTTP, $replacementWWW], $text);
        }
        return $text;
    }

    public static function getFontAwesomeIcon($name = null) {
        $PREFIX_TO_STYLE = [
            'fas' => 'solid',
            'far' => 'regular',
            'fab' => 'brands',
            'fa' => 'solid'
        ];

        if (null !== $name) {

            $icon = explode(" ", $name);
            $family = $PREFIX_TO_STYLE[$icon[0]];
            $iconName = str_replace("fa-", "", $icon[1]);

            $file = __DIR__ . '/../../../../public/static/assets/svgs/font-awesome/' . $family . '/' . $iconName . '.svg';

            if (file_exists($file)) {
                return '<span class="icon fontawesome-icon icon-' . $iconName . '">' . file_get_contents($file) . '</span>';
            }
        }
        return null;
    }

}
