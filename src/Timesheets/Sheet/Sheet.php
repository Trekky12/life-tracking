<?php

namespace App\Timesheets\Sheet;

class Sheet extends \App\Base\Model {

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
        $this->diff = $this->exists('diff', $data) ? filter_var($data['diff'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->notice = $this->exists('notice', $data) ? trim(filter_var($data['notice'], FILTER_SANITIZE_STRING)) : null;

        $this->start_lat = $this->exists('start_lat', $data) ? filter_var($data['start_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->start_lng = $this->exists('start_lng', $data) ? filter_var($data['start_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->start_acc = $this->exists('start_acc', $data) ? filter_var($data['start_acc'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->end_lat = $this->exists('end_lat', $data) ? filter_var($data['end_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->end_lng = $this->exists('end_lng', $data) ? filter_var($data['end_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->end_acc = $this->exists('end_acc', $data) ? filter_var($data['end_acc'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;


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

    public function getDiff($fallback = null) {
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
        } else {
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

}
