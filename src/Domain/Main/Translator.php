<?php

namespace App\Domain\Main;

use App\Domain\Base\Settings;
use Symfony\Component\Translation\Translator as SymfonyTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;

class Translator {

    protected $settings;
    protected $translator;

    public function __construct(Settings $settings) {
        $this->settings = $settings;

        $selectedLanguage = $this->settings->getAppSettings()['i18n']['template'];

        $this->translator = new SymfonyTranslator($selectedLanguage);
        $this->translator->addLoader('array', new ArrayLoader());

        $langFile = __DIR__ . '/../lang/' . $selectedLanguage . '.php';
        if (file_exists($langFile)) {
            $translations = require $langFile;
            $this->translator->addResource('array', $translations, $selectedLanguage);
        }

    }

    public function getTranslatedString(string $key, array $parameters = []): string {
        return $this->translator->trans($key, $parameters);
    }

    public function getSymfonyTranslator(): SymfonyTranslator {
        return $this->translator;
    }

}
