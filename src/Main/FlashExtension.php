<?php

namespace App\Main;

use Slim\Flash\Messages;

class FlashExtension extends \Twig\Extension\AbstractExtension {

    /**
     * @var Messages
     */
    protected $flash;

    /**
     * Constructor.
     *
     * @param Messages $flash the Flash messages service provider
     */
    public function __construct(Messages $flash) {
        $this->flash = $flash;
    }

    /**
     * Extension name.
     *
     * @return string
     */
    public function getName() {
        return 'slim-twig-flash';
    }

    /**
     * Callback for twig.
     *
     * @return array
     */
    public function getFunctions() {
        return [
            new \Twig\TwigFunction('flash', [$this, 'getMessages']),
        ];
    }

    /**
     * Returns Flash messages; If key is provided then returns messages
     * for that key.
     *
     * @param string $key
     *
     * @return array
     */
    public function getMessages($key = null) {
        if (null !== $key) {
            return $this->flash->getMessage($key);
        }

        return $this->flash->getMessages();
    }

}
