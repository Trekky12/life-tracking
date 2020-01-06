<?php

namespace Tests\Functional\Base;

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
    protected $useCRSF = true;

    /**
     * save login token
     * @var string
     */
    protected static $token = null;
    protected $backupGlobalsBlacklist = array('_SESSION');

    /**
     * Routes
     */
    protected $uri_overview = "";
    protected $uri_edit = "";
    protected $uri_save = "";
    protected $uri_delete = "";
    protected $uri_view = "";
    protected $uri_childs_edit = "";
    protected $uri_childs_save = "";
    protected $uri_childs_delete = "";

    public static function setUpBeforeClass(): void {
        
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
        $settings = $this->getSettings();

        $settings["settings"]["CSRF"]["enabled"] = $this->useCRSF;

        // Instantiate the application
        $app = new App($settings);
        // Set up dependencies
        require __DIR__ . '/../../../src/dependencies.php';
        // Register middleware
        if ($this->withMiddleware) {
            require __DIR__ . '/../../../src/middleware.php';
        }
        // Register routes
        require __DIR__ . '/../../../src/routes.php';
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

    protected function getSettings() {
        return require __DIR__ . '/../../../src/settings.php';
    }

    protected function getAppSettings() {
        $settings = $this->getSettings();
        return $settings['settings']['app'];
    }

    protected function extractFormCSRF($response) {
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

    protected function extractJSCSRF($response) {
        /**
          <script type='text/javascript' >
          var allowedReload = false;
          ...
          var tokens = [{"csrf_name":"csrf5dd248a11c3d7","csrf_value":"8cbaf7be48b18d3b60b9a413f7451218"},{"csrf_name":"csrf5dd248a11c5cb","csrf_value":"c5878fa1c43f07b91b6ba36d6c8d8277"}];
          </script>
         */
        $matches = [];
        $body = (string) $response->getBody();
        $re = '/var tokens = \[\{\"csrf_name\":\"(?<csrf_name>[a-z0-9]*)\",\"csrf_value\":\"(?<csrf_value>[a-z0-9]*)\"\},\{\"csrf_name\":\"(?<csrf2_name>[a-z0-9]*)\",\"csrf_value\":\"(?<csrf2_value>[a-z0-9]*)\"\}\];/s';
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
        $csrf_data = $this->extractFormCSRF($response);
        $this->postLoginPage($csrf_data, $user, $password);
    }

    public function logout() {
        $this->getLogout();
    }

    /**
     * 
     */
    protected function getCSRFTokens($csrf_data) {
        $response = $this->runApp('POST', '/tokens', array_merge(array("count" => 10), $csrf_data));
        $tokens = json_decode((string) $response->getBody(), true);
        return $tokens;
    }

    protected function getURIView($hash) {
        return str_replace("HASH", $hash, $this->uri_view);
    }

    protected function getURIChildEdit($hash) {
        return str_replace("HASH", $hash, $this->uri_child_edit);
    }

    protected function getURIChildSave($hash) {
        return str_replace("HASH", $hash, $this->uri_child_save);
    }

    protected function getURIChildDelete($hash) {
        return str_replace("HASH", $hash, $this->uri_child_delete);
    }

}
