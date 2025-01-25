<?php

namespace App\Domain\Home\Widget;

use App\Domain\Main\Translator;
use App\Domain\Main\Utility\WeatherUtility;

class CurrentWeatherWidget implements Widget {

    private $translation;

    public function __construct(Translator $translation) {
        $this->translation = $translation;
    }

    public function getContent(?WidgetObject $widget = null) {
        return null;
    }

    public function getTitle(?WidgetObject $widget = null) {
        return $widget->getOptions()["title"];
    }

    public function getOptions(?WidgetObject $widget = null) {
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

    public function getLink(?WidgetObject $widget = null) {
        return null;
    }

    public static function formatCurrentWeatherRequestData($status, $result)
    {

        $weather = [];

        if ($status == 200) {

            // Convert unicode 
            // @see https://stackoverflow.com/a/2577882
            $result = mb_convert_encoding($result, 'HTML-ENTITIES');

            $response = json_decode($result, true);

            $weather["temp"] = round($response["main"]["temp"], 1);
            $weather["description"] = $response["weather"][0]["description"];
            $weather["icon"] = WeatherUtility::getWeatherIcon($response["weather"][0]["icon"]);
        }

        return $weather;
    }

}
