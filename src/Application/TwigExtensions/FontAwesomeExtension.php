<?php

namespace App\Application\TwigExtensions;

use App\Domain\Main\Utility\Utility;

class FontAwesomeExtension extends \Twig\Extension\AbstractExtension {

    public function getName() {
        return 'slim-twig-fontawesome5';
    }

    /**
     * Callback for twig.
     *
     * @return array
     */
    public function getFunctions() {
        return [
            new \Twig\TwigFunction('fontawesome', [$this, 'getIcon'], [
                'is_safe' => ['html'],
                    ]),
        ];
    }

    public function getIcon($name = null, $rotate = false) {
        return Utility::getFontAwesomeIcon($name, $rotate);
    }

}
