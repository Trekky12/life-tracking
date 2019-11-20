<?php

namespace App\Main;

class CsrfExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface {

    /**
     * @var \Slim\Csrf\Guard
     */
    protected $csrf;

    public function __construct(\Slim\Csrf\Guard $csrf) {
        $this->csrf = $csrf;
    }

    public function getGlobals() {
        // CSRF token name and value
        $csrfNameKey = $this->csrf->getTokenNameKey();
        $csrfValueKey = $this->csrf->getTokenValueKey();
        $csrfName = $this->csrf->getTokenName();
        $csrfValue = $this->csrf->getTokenValue();
        
        $jsTokensCount = 2;
        $jsTokens = [];
        for ($i = 0; $i < $jsTokensCount; $i++) {
            $jsTokens[] = $this->csrf->generateToken();
        }

        return [
            'csrf' => [
                'keys' => [
                    'name' => $csrfNameKey,
                    'value' => $csrfValueKey
                ],
                'name' => $csrfName,
                'value' => $csrfValue
            ],
            'csrf_js' => $jsTokens
        ];
    }

    public function getName() {
        return 'slim/csrf';
    }

}
