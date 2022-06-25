<?php

namespace App\Domain\Main\Utility;

class WeatherUtility
{

    public static function getWeatherIcon($name)
    {
        $iconTable = [
            "01d" => "wi-day-sunny",
            "02d" => "wi-day-cloudy",
            "03d" => "wi-cloudy",
            "04d" => "wi-cloudy-windy",
            "09d" => "wi-showers",
            "10d" => "wi-rain",
            "11d" => "wi-thunderstorm",
            "13d" => "wi-snow",
            "50d" => "wi-fog",
            "01n" => "wi-night-clear",
            "02n" => "wi-night-cloudy",
            "03n" => "wi-night-cloudy",
            "04n" => "wi-night-cloudy",
            "09n" => "wi-night-showers",
            "10n" => "wi-night-rain",
            "11n" => "wi-night-thunderstorm",
            "13n" => "wi-night-snow",
            "50n" => "wi-night-alt-cloudy-windy"
        ];

        return $iconTable[$name];
    }
}
