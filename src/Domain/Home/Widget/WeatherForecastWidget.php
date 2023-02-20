<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Main\Utility\WeatherUtility;

class WeatherForecastWidget implements Widget {

    private $logger;
    private $translation;

    public function __construct(LoggerInterface $logger, Translator $translation) {
        $this->logger = $logger;
        $this->translation = $translation;

    }

    public function getContent(WidgetObject $widget = null) {
        return null;
    }

    public function getTitle(WidgetObject $widget = null) {
        return $widget->getOptions()["title"];
    }

    public function getOptions(WidgetObject $widget = null) {
        return [
            [
                "label" => $this->translation->getTranslatedString("WIDGET_TITLE"),
                "value" => !is_null($widget) ? $widget->getOptions()["title"] : null,
                "name" => "title",
                "type" => "input"
            ],
            [
                "label" => $this->translation->getTranslatedString("WIDGET_URL"),
                "value" => !is_null($widget) ? $widget->getOptions()["url"] : null,
                "name" => "url",
                "type" => "input"
            ]
        ];
    }

    public function getLink(WidgetObject $widget = null) {
        null;
    }

    public static function formatWeatherForecastRequestData($status, $result, $formatter)
    {

        $weather = [];

        if ($status == 200) {

            // Convert unicode 
            // @see https://stackoverflow.com/a/2577882
            $result = mb_convert_encoding($result, 'HTML-ENTITIES');

            $response = json_decode($result, true);

            foreach ($response["list"] as $forecast) {
                $date = \DateTime::createFromFormat('U', $forecast["dt"]);

                $weather[] = [
                    "weekday" => $formatter->format($date),
                    "icon" => WeatherUtility::getWeatherIcon($forecast["weather"][0]["icon"]),
                    "description" => $forecast["weather"][0]["description"],
                    "minTemp" => round($forecast["temp"]["min"], 1),
                    "maxTemp" => round($forecast["temp"]["max"], 1),
                ];
            }
        }

        return $weather;
    }

}
