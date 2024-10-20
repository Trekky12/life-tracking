<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;

class EFAWidget implements Widget
{

    private $logger;
    private $translation;

    public function __construct(LoggerInterface $logger, Translator $translation)
    {
        $this->logger = $logger;
        $this->translation = $translation;
    }

    public function getContent(WidgetObject $widget = null)
    {
        return null;
    }

    public function getTitle(WidgetObject $widget = null)
    {
        return $widget->getOptions()["title"];
    }

    public function getOptions(WidgetObject $widget = null)
    {
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

    public function getLink(WidgetObject $widget = null)
    {
        return null;
    }

    public static function formatEFARequestData($status, $result)
    {

        $departures = [];

        if ($status == 200) {
            libxml_use_internal_errors(true);
            $xml_result = [];
            $xml = simplexml_load_string($result);

            if ($xml !== false) {
                foreach ($xml->itdDepartureMonitorRequest[0]->itdDepartureList[0]->itdDeparture as $departure) {
                    $dep = [];

                    $dep["servingLine"]["number"] = (string) $departure->itdServingLine->attributes()->number;
                    $dep["servingLine"]["direction"] = (string) $departure->itdServingLine->attributes()->direction;
                    $dep["servingLine"]["delay"] = (int) $departure->itdServingLine->itdNoTrain->attributes()->delay;

                    $dep["dateTime"]["year"] = (string) $departure->itdDateTime->itdDate->attributes()->year;
                    $dep["dateTime"]["month"] = (string) $departure->itdDateTime->itdDate->attributes()->month;
                    $dep["dateTime"]["day"] = (string) $departure->itdDateTime->itdDate->attributes()->day;
                    $dep["dateTime"]["hour"] = (string) $departure->itdDateTime->itdTime->attributes()->hour;
                    $dep["dateTime"]["minute"] = (string) $departure->itdDateTime->itdTime->attributes()->minute;

                    if (isset($departure->itdRTDateTime)) {
                        $dep["realDateTime"]["year"] = (string) $departure->itdRTDateTime->itdDate->attributes()->year;
                        $dep["realDateTime"]["month"] = (string) $departure->itdRTDateTime->itdDate->attributes()->month;
                        $dep["realDateTime"]["day"] = (string) $departure->itdRTDateTime->itdDate->attributes()->day;
                        $dep["realDateTime"]["hour"] = (string) $departure->itdRTDateTime->itdTime->attributes()->hour;
                        $dep["realDateTime"]["minute"] = (string) $departure->itdRTDateTime->itdTime->attributes()->minute;
                    }

                    $dep["realtimeTripStatus"] = (string) $departure->attributes()->realtimeTripStatus;
                    $dep["realtimeStatus"] = (string) $departure->attributes()->realtimeStatus;

                    $xml_result["departureList"][] = $dep;
                }
                $result = json_encode($xml_result);

                // Convert unicode 
                // @see https://stackoverflow.com/a/2577882
                $result = mb_convert_encoding($result, 'HTML-ENTITIES');
            }

            $response = json_decode($result, true);

            foreach ($response["departureList"] as $departure) {

                $dateTime = new \DateTime();
                $dateTime->setDate($departure["dateTime"]["year"], $departure["dateTime"]["month"], $departure["dateTime"]["day"]);
                $dateTime->setTime($departure["dateTime"]["hour"], $departure["dateTime"]["minute"]);
                $calculatedDelay = 0;


                if (array_key_exists("realDateTime", $departure)) {
                    $realDateTime = new \DateTime();
                    $realDateTime->setDate($departure["realDateTime"]["year"], $departure["realDateTime"]["month"], $departure["realDateTime"]["day"]);
                    $realDateTime->setTime($departure["realDateTime"]["hour"], $departure["realDateTime"]["minute"]);
                    $departureTime = $realDateTime;
                    $calculatedDelay = ($realDateTime->getTimestamp() - $dateTime->getTimestamp()) / 1000 / 60;
                } else {
                    $departureTime = $dateTime;
                }

                $time = $departureTime->format('H:i');

                $delay = '';
                if ($departure["servingLine"]["delay"] > 0) {
                    $delay = $departure["servingLine"]["delay"];
                } else if ($calculatedDelay !== 0) {
                    $delay = $calculatedDelay;
                }

                $cancelled = false;
                if(array_key_exists("realtimeStatus", $departure) && str_contains($departure["realtimeStatus"], "CANCELLED")){
                    $cancelled = true;
                }
                if(array_key_exists("realtimeTripStatus", $departure) && str_contains($departure["realtimeTripStatus"], "CANCELLED")){
                    $cancelled = true;
                }


                $departures[] = [
                    "line" => $departure["servingLine"]["number"],
                    "direction" => $departure["servingLine"]["direction"],
                    "time" => $time,
                    "delay" => $delay,
                    "cancelled" => $cancelled ? "CANCELLED" : ""
                ];
            }
        }

        return $departures;
    }
}
