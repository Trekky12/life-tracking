<?php

namespace App\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

class MainController {

    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function getDatatableLang(Request $request, Response $response) {
        $lang = $this->ci->get('settings')['app']['i18n']['datatables'];

        $file = file_get_contents(__DIR__ . '/../lang/dataTables/' . $lang);

        /**
         * Remove comments from file
         * @see https://stackoverflow.com/a/19136663
         */
        $file = preg_replace('!^[ \t]*/\*.*?\*/[ \t]*[\r\n]!s', '', $file);

        $json = json_decode($file);

        return $response->withJson($json);
    }

    public function index(Request $request, Response $response) {
        return $this->ci->get('view')->render($response, 'index.twig', []);
    }

}
