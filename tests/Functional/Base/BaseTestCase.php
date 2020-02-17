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
     * Variables
     */
    protected $USE_GUZZLE = true;
    protected $USER_AGENT = 'PHPUnit Test';

    /**
     * save login token
     * @var string
     */
    protected static $LOGIN_TOKEN = null;
    protected static $SESSION = null;
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

    /**
     * CSRF Tokens
     */
    private $tokens = [];

    public static function setUpBeforeClass(): void {
        
    }

    public function request($requestMethod, $requestUri, $requestData = [], $auth = array(), $form_data = null) {
        if ($this->USE_GUZZLE) {
            return $this->HTTP_request($requestMethod, $requestUri, $requestData, $auth, $form_data);
        } else {
            return $this->runApp($requestMethod, $requestUri, $requestData, $auth, $form_data);
        }
    }

    public function HTTP_request($requestMethod, $requestUri, $requestData = [], $auth = array(), $form_data = null) {

        $client = new \GuzzleHttp\Client([
            'proxy' => '',
            'allow_redirects' => false,
            'http_errors' => false,
            'base_uri' => 'http://tracking.localhost/',
            'cookies' => true
        ]);

        // add csrf token
        if ($requestMethod != 'GET' && count($this->tokens) > 0) {
            $requestData = $requestData + $this->getToken();
        }

        // Add request data, if it exists
        $headers = ['User-Agent' => $this->USER_AGENT];
        $body = null;
        if (!empty($requestData)) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            $body = http_build_query($requestData);
        }

        // handle form data
        if (isset($form_data)) {
            $headers = [];
            $multipart_data = [];
            foreach ($form_data as $fData) {
                $multipart_data[] = [
                    'name' => $fData['name'],
                    'contents' => fopen($fData['contents'], 'r'),
                    'filename' => $fData['filename']
                ];
            }
            foreach ($requestData as $rKey => $rData) {
                $multipart_data[] = [
                    'name' => $rKey,
                    'contents' => $rData,
                ];
            }
            $body = new \GuzzleHttp\Psr7\MultipartStream($multipart_data);
        }

        $request = new \GuzzleHttp\Psr7\Request($requestMethod, $requestUri, $headers, $body);

        if (!empty(self::$LOGIN_TOKEN)) {
            $request = FigRequestCookies::set($request, Cookie::create('token', self::$LOGIN_TOKEN));
        }
        if (!empty(self::$SESSION)) {
            $request = FigRequestCookies::set($request, Cookie::create('PHPSESSID', self::$SESSION));
        }

        if (isset($auth['user'])) {
            $request = $request->withHeader('Authorization', 'Basic ' . base64_encode("${auth['user']}:${auth['pass']}"));
        }

        $response = $client->send($request);

        // Save Token 
        $setCookies = SetCookies::fromResponse($response);
        $setTokenCookie = $setCookies->get('token');
        if (!is_null($setTokenCookie)) {
            self::$LOGIN_TOKEN = $setTokenCookie->getValue();
        }

        $setSESSIONCookie = $setCookies->get('PHPSESSID');
        if (!is_null($setSESSIONCookie)) {
            self::$SESSION = $setSESSIONCookie->getValue();
        }

        // Return the response
        return $response;
    }

    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string $requestUri the request URI
     * @param array|object|null $requestData the request data
     * @return \Slim\Http\Response
     */
    public function runApp($requestMethod, $requestUri, $requestData = null, $auth = array(), $form_data = null) {

        // Create a mock environment for testing with
        $environment = Environment::mock(
                        [
                            'REQUEST_METHOD' => $requestMethod,
                            'REQUEST_URI' => $requestUri
                        ]
        );
        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // add csrf token
        if ($requestMethod != 'GET' && count($this->tokens) > 0) {
            $requestData = $requestData + $this->getToken();
        }

        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        if (!empty(self::$LOGIN_TOKEN)) {
            $request = FigRequestCookies::set($request, Cookie::create('token', self::$LOGIN_TOKEN));
        }

        if (isset($auth['user'])) {
            $request = $request->withHeader('Authorization', 'Basic ' . base64_encode("${auth['user']}:${auth['pass']}"));
        }

        // handle form data
        if (isset($form_data)) {
            $files = [];
            foreach ($form_data as $f) {
                // create a copy which could be moved
                $destinationFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $f['filename'];
                copy($f['contents'], $destinationFile);

                $files[$f['name']] = new \Slim\Http\UploadedFile($destinationFile, $f['filename'], 'image/png', filesize($f['contents']));
            }
            $request = $request->withUploadedFiles($files);
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
            self::$LOGIN_TOKEN = $setTokenCookie->getValue();
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

    /**
     * Helper functions for login/logout on other tests
     */
    public function login($user, $password) {
        $response = $this->request('GET', '/login');
        $csrf_token = $this->extractFormCSRF($response);

        $data = [
            "username" => $user,
            "password" => $password
        ];
        $this->request('POST', '/login', array_merge($data, $csrf_token));

        // get initial CSRF token
        $response_home = $this->request('GET', '/');
        $this->tokens[] = $this->extractJSCSRF($response_home);
    }

    public function logout() {
        $this->request('GET', '/logout');
    }

    /**
     * Replace HASH in routes
     */
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

    /**
     * CSRF Tokens
     */
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

    /**
     * Get new tokens from endpoint /tokens
     */
    protected function getCSRFTokens($csrf_token, $count = 10) {
        $response = $this->request('POST', '/tokens', array_merge(array("count" => $count), $csrf_token));
        $tokens = json_decode((string) $response->getBody(), true);
        return $tokens;
    }

    public function getToken() {

        if (!is_array($this->tokens) || count($this->tokens) < 1) {
            throw new \Exception("No token available");
        }

        // take available token
        if (count($this->tokens) > 1) {
            return array_pop($this->tokens);
        }

        // get new tokens
        $token = array_pop($this->tokens);
        $this->tokens = $this->getCSRFTokens($token);
        return $this->getToken();
    }

    /**
     * Extract input fields from page
     * @see https://stackoverflow.com/a/1274074
     */
    protected function getInputFields($body) {
        $input_fields = [];
        $dom = new \DOMDocument();
        if (@$dom->loadHTML($body)) {
            $xpath = new \DOMXpath($dom);

            $nodeList = $xpath->query('//input');
            foreach ($nodeList as $node) {
                $name = $node->getAttribute('name');
                $value = $node->getAttribute('value');
                if ($node->getAttribute('type') == "checkbox" || $node->getAttribute('type') == "radio") {
                    $value = $node->hasAttribute('checked') ? 1 : 0;
                }

                $input_fields[$name] = $value;
            }
        }
        return $input_fields;
    }

    protected function compareInputFields($body, $data) {
        $input_fields = $this->getInputFields($body);

        foreach ($data as $key => $val) {
            $this->assertArrayHasKey($key, $input_fields, $key . " missing");
            $this->assertSame($input_fields[$key], $val, "Field: " . $key . ", Expected: " . $val . ", Actual: " . $input_fields[$key]);
        }
    }

}
