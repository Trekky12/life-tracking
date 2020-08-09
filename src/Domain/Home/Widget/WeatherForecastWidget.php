<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;

class WeatherForecastWidget implements Widget {

    private $logger;
    private $translation;

    public function __construct(LoggerInterface $logger, Translator $translation) {
        $this->logger = $logger;
        $this->translation = $translation;

    }

    public function getContent(WidgetObject $widget = null) {
        return [
            "url" => $widget->getOptions()["url"]
        ];
    }

    public function getTitle(WidgetObject $widget = null) {
        return $widget->getOptions()["title"];
    }

    public function getOptions() {
        return [
            [
                "label" => $this->translation->getTranslatedString("WIDGET_TITLE"),
                "value" => null,
                "name" => "title",
                "type" => "input"
            ],
            [
                "label" => $this->translation->getTranslatedString("WIDGET_URL"),
                "value" => null,
                "name" => "url",
                "type" => "input"
            ]
        ];
    }

    public function getLink(WidgetObject $widget = null) {
        null;
    }

}
