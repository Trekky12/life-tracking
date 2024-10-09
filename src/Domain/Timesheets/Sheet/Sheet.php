<?php

namespace App\Domain\Timesheets\Sheet;

use App\Domain\Main\Utility\DateUtility;

class Sheet extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_SHEET";

    public function parseData(array $data) {

        // new dataset --> save createdBy 
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }
        // is later overwritten with db value (if exists)
        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->project = $this->exists('project', $data) ? filter_var($data['project'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->start = $this->exists('start', $data) ? filter_var($data['start'], FILTER_SANITIZE_STRING) : null;
        $this->end = $this->exists('end', $data) ? filter_var($data['end'], FILTER_SANITIZE_STRING) : null;
        $this->duration = $this->exists('duration', $data) ? filter_var($data['duration'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->duration_modified = $this->exists('duration_modified', $data) ? filter_var($data['duration_modified'], FILTER_SANITIZE_NUMBER_INT) : null;

        $set_duration_modified = $this->exists('set_duration_modified', $data) ? filter_var($data['set_duration_modified'], FILTER_SANITIZE_STRING) : null;
        if (!is_null($set_duration_modified)) {
            $this->duration_modified = DateUtility::getSecondsFromDuration($set_duration_modified);
        }

        $this->start_lat = $this->exists('start_lat', $data) ? filter_var($data['start_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->start_lng = $this->exists('start_lng', $data) ? filter_var($data['start_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->start_acc = $this->exists('start_acc', $data) ? filter_var($data['start_acc'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->end_lat = $this->exists('end_lat', $data) ? filter_var($data['end_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->end_lng = $this->exists('end_lng', $data) ? filter_var($data['end_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->end_acc = $this->exists('end_acc', $data) ? filter_var($data['end_acc'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;


        $this->categories = $this->exists('categories', $data) ? filter_var($data['categories'], FILTER_SANITIZE_STRING) : null;

        $this->is_billed = $this->exists('is_billed', $data) ? filter_var($data['is_billed'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->is_payed = $this->exists('is_payed', $data) ? filter_var($data['is_payed'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->is_planned = $this->exists('is_planned', $data) ? filter_var($data['is_planned'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->customer = $this->exists('customer', $data) ? filter_var($data['customer'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->customerName = $this->exists('customerName', $data) ? filter_var($data['customerName'], FILTER_SANITIZE_STRING) : null;

        /* if (empty($this->name) && $this->settleup == 0) {
          $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
          } */
    }

    public function getStartDateTime($fallback = null) {
        $start = new \DateTime($this->start);
        return !is_null($this->start) ? $start : $fallback;
    }

    public function getEndDateTime($fallback = null) {
        $end = new \DateTime($this->end);
        return !is_null($this->end) ? $end : $fallback;
    }

    public function calculateDuration($fallback = null) {
        $start = $this->getStartDateTime();
        $end = $this->getEndDateTime();

        return !is_null($this->start) && !is_null($this->end) ? $end->getTimestamp() - $start->getTimestamp() : $fallback;
    }

    public function getDateStartEnd($language, $dateFormat, $datetimeShortFormat, $timeFormat) {

        $fmtDate = new \IntlDateFormatter($language, NULL, NULL);
        $fmtDate->setPattern($dateFormat);

        $fmtDateTime = new \IntlDateFormatter($language, NULL, NULL);
        $fmtDateTime->setPattern($datetimeShortFormat);

        $fmtTime = new \IntlDateFormatter($language, NULL, NULL);
        $fmtTime->setPattern($timeFormat);

        $date = '';
        $start = '';
        $end = '';

        // only show time on end date when start date and end date are on the same day
        if (!is_null($this->start) && !is_null($this->end) && $this->getStartDateTime()->format('Y-m-d') == $this->getEndDateTime()->format('Y-m-d')) {
            $end = $fmtTime->format($this->getEndDateTime());
        } elseif (!is_null($this->end)) {
            $end = $fmtDateTime->format($this->getEndDateTime());
        }

        if (!is_null($this->start)) {
            $date = $fmtDate->format($this->getStartDateTime());
            $start = $fmtTime->format($this->getStartDateTime());
        } elseif (!is_null($this->end)) {
            $date = $fmtDate->format($this->getEndDateTime());
            $end = $fmtTime->format($this->getEndDateTime());
        }

        return array($date, $start, $end);
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        $language = $settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $settings->getAppSettings()['i18n']['dateformatPHP'];

        $fmtDateTime = new \IntlDateFormatter($language, NULL, NULL);
        $fmtDateTime->setPattern($dateFormatPHP['datetime']);


        if (!is_null($this->start) && !is_null($this->end)) {
            $type = $translator->getTranslatedString('TIMESHEETS_FAST_PROJECT_BASED');

            $start = $fmtDateTime->format($this->getStartDateTime());
            $end = $fmtDateTime->format($this->getEndDateTime());

            $date = sprintf("%s - %s", $start, $end);
        } elseif (!is_null($this->start)) {
            $date = $fmtDateTime->format($this->getStartDateTime());
            $type = $translator->getTranslatedString('TIMESHEETS_COME_PROJECT_BASED');
        } elseif (!is_null($this->end)) {
            $date = $fmtDateTime->format($this->getEndDateTime());
            $type = $translator->getTranslatedString('TIMESHEETS_LEAVE_PROJECT_BASED');
        }

        return sprintf("%s (%s)", $date, $type);
    }

    public function getParentID() {
        return $this->project;
    }

    public function get_fields($remove_user_element = false, $insert = true, $update = false) {
        $temp = parent::get_fields($remove_user_element, $insert, $update);

        unset($temp["categories"]);
        unset($temp["customerName"]);

        return $temp;
    }

    public function getDurationModification($project_conversion_rate = 1) {
        if ($this->duration == $this->duration_modified) {
            return 0;
        }

        if ($this->duration * $project_conversion_rate == $this->duration_modified) {
            return 1;
        }

        return 2;
    }

}
