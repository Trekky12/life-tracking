<?php

namespace App\Domain\Main\Utility;

use App\Domain\Base\Settings;

class DateUtility {

    public static function getMonthName(Settings $settings, $month) {
        $language = $settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $settings->getAppSettings()['i18n']['dateformatPHP'];

        $fmt = new \IntlDateFormatter($language);
        $fmt->setPattern($dateFormatPHP['month_name']);

        $dateObj = \DateTime::createFromFormat('!m', $month);
        return $fmt->format($dateObj);
    }

    public static function getDay(Settings $settings, $date) {
        $language = $settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $settings->getAppSettings()['i18n']['dateformatPHP'];

        $fmt = new \IntlDateFormatter($language);
        $fmt->setPattern($dateFormatPHP['date']);

        $dateObj = $d = new \DateTime($date);
        return $fmt->format($dateObj);
    }

    public static function getDateRange($data, $defaultFrom = 'today', $defaultTo = 'today') {

        if (strcmp($defaultFrom ?? '', 'today') === 0) {
            $defaultFrom = date('Y-m-d');
        }
        if (strcmp($defaultTo ?? '', 'today') === 0) {
            $defaultTo = date('Y-m-d');
        }

        $from = array_key_exists('from', $data) && !empty($data['from']) ? Utility::filter_string_polyfill($data['from']) : $defaultFrom;
        $to = array_key_exists('to', $data) && !empty($data['to']) ? Utility::filter_string_polyfill($data['to']) : $defaultTo;

        /**
         * Clean dates
         */
        $dateRegex = "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/";
        if ((!is_null($from) && !preg_match($dateRegex, $from)) || (!is_null($to) && !preg_match($dateRegex, $to))) {

            $from = !is_null($from) && preg_match($dateRegex, $from) ? $from : $defaultFrom;
            $to = !is_null($to) && preg_match($dateRegex, $to) ? $to : $defaultTo;
        }

        return array($from, $to);
    }

    public static function splitDateInterval($total_seconds, $hide_seconds = false) {

        if (is_null($total_seconds)) {
            return '';
        }

        $prefix = $total_seconds < 0 ? "-" : "";

        $total_seconds = abs($total_seconds);

        $total_minutes = $total_seconds / 60;
        $hours = intval($total_minutes / 60);
        $minutes = intval($total_minutes - $hours * 60);
        $seconds = intval($total_seconds - $total_minutes * 60);

        return $prefix . ($hide_seconds ? sprintf('%02d:%02d', $hours, $minutes) : sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds));
    }

    public static function getSecondsFromDuration($value) {
        if (is_null($value)) {
            return null;
        }
        $matches = [];
        preg_match("/([0-9]+):([0-9]{2})/", $value, $matches);
        if (count($matches) == 3) {
            return intval($matches[1]) * 60 * 60 + intval($matches[2]) * 60;
        }

        return intval($value);
    }
}
