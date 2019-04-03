<?php

namespace Tests\Functional;

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;
use PHPUnit\Framework\TestCase;
use Dflydev\FigCookies\SetCookies;
use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;

/**
 * This is an example class that shows how you could set up a method that
 * runs the application. Note that it doesn't cover all use-cases and is
 * tuned to the specifics of this skeleton app, so if your needs are
 * different, you'll need to change it.
 */
// running from the cli doesn't set $_SESSION here on phpunit trunk                                                                                                
// @see https://stackoverflow.com/a/9375476
if (!isset($_SESSION))
    $_SESSION = array();

class BaseTestCase extends TestCase {

    /**
     * Use middleware when running application?
     *
     * @var bool
     */
    protected $withMiddleware = true;

    /**
     * save login token
     * @var string
     */
    protected static $token = null;
    protected $backupGlobalsBlacklist = array('_SESSION');

    public static function setUpBeforeClass(): void {   
    }

    protected static function getDatabase() {
        $settings = require __DIR__ . '/../../src/settings.php';
        $db_settings = $settings["settings"]["db"];
        try {
            $pdo = new \PDO("mysql:host=" . $db_settings['host'] . ";dbname=" . $db_settings['dbname'], $db_settings['user'], $db_settings['pass']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $pdo->exec("set names utf8");
            return $pdo;
        } catch (\PDOException $e) {
            die("No access to database");
        }
    }

    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string $requestUri the request URI
     * @param array|object|null $requestData the request data
     * @return \Slim\Http\Response
     */
    public function runApp($requestMethod, $requestUri, $requestData = null, $auth = array()) {

        // Create a mock environment for testing with
        $environment = Environment::mock(
                        [
                            'REQUEST_METHOD' => $requestMethod,
                            'REQUEST_URI' => $requestUri
                        ]
        );
        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);
        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        if (!empty(self::$token)) {
            $request = FigRequestCookies::set($request, Cookie::create('token', self::$token));
        }

        if (isset($auth['user'])) {
            $request = $request->withHeader('Authorization', 'Basic ' . base64_encode("${auth['user']}:${auth['pass']}"));
        }


        // Set up a response object
        $response = new Response();
        // Use the application settings
        $settings = require __DIR__ . '/../../src/settings.php';
        // Instantiate the application
        $app = new App($settings);
        // Set up dependencies
        require __DIR__ . '/../../src/dependencies.php';
        // Register middleware
        if ($this->withMiddleware) {
            require __DIR__ . '/../../src/middleware.php';
        }
        // Register routes
        require __DIR__ . '/../../src/routes.php';
        // Process the application
        $response = $app->process($request, $response);

        // Save Token 
        $setCookies = SetCookies::fromResponse($response);
        $setTokenCookie = $setCookies->get('token');
        if (!is_null($setTokenCookie)) {
            self::$token = $setTokenCookie->getValue();
        }

        // Return the response
        return $response;
    }

    protected function extractCSRF($response) {
        /**
          <input type="hidden" name="csrf_name" value="csrf5c94b1958ed33">
          <input type="hidden" name="csrf_value" value="1a083321a891731ed2747360f572c934">
         */
        $matches = [];
        $body = (string) $response->getBody();
        $re = '/<input type="hidden" name="csrf_name" value="(?<csrf_name>.*)?">(\s)*<input type="hidden" name="csrf_value" value="(?<csrf_value>.*)?">/m';
        preg_match($re, $body, $matches);

        $csrf_name = $matches["csrf_name"];
        $csrf_value = $matches["csrf_value"];

        return array("csrf_name" => $csrf_name, "csrf_value" => $csrf_value);
    }

    protected function getLoginPage() {
        return $this->runApp('GET', '/login');
    }

    protected function postLoginPage(array $csrf_data, $user, $password) {
        $data = ["username" => $user, "password" => $password];
        return $this->runApp('POST', '/login', array_merge($data, $csrf_data));
    }

    protected function getLogout() {
        return $this->runApp('GET', '/logout');
    }

    /**
     * Helper functions for login/logout on other tests
     */
    public function login($user, $password) {
        $response = $this->getLoginPage();
        $csrf_data = $this->extractCSRF($response);
        $this->postLoginPage($csrf_data, $user, $password);
    }

    public function logout() {
        $this->getLogout();
    }

}
