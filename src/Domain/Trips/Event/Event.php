<?php

namespace App\Domain\Trips\Event;

use App\Domain\Main\Utility\Utility;

class Event extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TRIPS_EVENT";

    public function parseData(array $data) {

        // new dataset --> save createdBy 
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }

        $this->trip = $this->exists('trip', $data) ? filter_var($data['trip'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;

        $this->start_date = $this->exists('start_date', $data) ? Utility::filter_string_polyfill($data['start_date']) : null;
        $this->start_time = $this->exists('start_time', $data) ? Utility::filter_string_polyfill($data['start_time']) : null;
        $this->start_address = $this->exists('start_address', $data) ? Utility::filter_string_polyfill($data['start_address']) : null;
        $this->start_lat = $this->exists('start_lat', $data) ? filter_var($data['start_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->start_lng = $this->exists('start_lng', $data) ? filter_var($data['start_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->end_date = $this->exists('end_date', $data) ? Utility::filter_string_polyfill($data['end_date']) : null;
        $this->end_time = $this->exists('end_time', $data) ? Utility::filter_string_polyfill($data['end_time']) : null;
        $this->end_address = $this->exists('end_address', $data) ? Utility::filter_string_polyfill($data['end_address']) : null;
        $this->end_lat = $this->exists('end_lat', $data) ? filter_var($data['end_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->end_lng = $this->exists('end_lng', $data) ? filter_var($data['end_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->notice = $this->exists('notice', $data) ? Utility::filter_string_polyfill($data['notice']) : null;

        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;

        $this->type = $this->exists('type', $data) ? Utility::filter_string_polyfill($data['type']) : null;

        if (!in_array($this->type, \App\Domain\Trips\Event\TripEventService::getEventTypes())) {
            $this->type = null;
        }

        /**
         * Clean date/time
         */
        if (!is_null($this->start_date) && !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->start_date)) {
            $this->start_date = null;
        }
        if (!is_null($this->start_time) && !preg_match("/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/", $this->start_time)) {
            $this->start_time = null;
        }
        if (!is_null($this->end_date) && !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->end_date)) {
            $this->end_date = null;
        }
        if (!is_null($this->end_time) && !preg_match("/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/", $this->end_time)) {
            $this->end_time = null;
        }

        // set end date to same date like start if end date is empty
        if (!empty($this->start_date) && empty($this->end_date)) {
            $this->end_date = $this->start_date;
        }

        // if start date is greater than end date swap both
        if (!empty($this->start_date) && !empty($this->end_date)) {
            $start = new \DateTime($this->start_date);
            $end = new \DateTime($this->end_date);

            if ($start > $end) {
                $this->start_date = $end->format('Y-m-d');
                $this->end_date = $start->format('Y-m-d');
            }
        }

        if ($this->exists('image', $data)) {
            $this->image = Utility::filter_string_polyfill($data['image']);
        }

        /* if (empty($this->name)) {
          $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
          } */
    }

    public function isFlight() {
        return strcmp($this->type ?? '', "FLIGHT") === 0;
    }

    public function isDrive() {
        return strcmp($this->type ?? '', "DRIVE") === 0;
    }

    public function isTrainride() {
        return strcmp($this->type ?? '', "TRAINRIDE") === 0;
    }

    public function isAccommodation() {
        return strcmp($this->type ?? '', "HOTEL") === 0;
    }

    public function isCarrental() {
        return strcmp($this->type ?? '', "CARRENTAL") === 0;
    }

    public function isEvent() {
        return strcmp($this->type ?? '', "EVENT") === 0;
    }

    public function isTravel() {
        return $this->isFlight() || $this->isTrainride() || $this->isDrive() || $this->isShip();
    }

    public function isWaypoint() {
        return strcmp($this->type ?? '', "WAYPOINT") === 0;
    }

    public function isShip() {
        return strcmp($this->type ?? '', "SHIP") === 0;
    }

    public function isWithoutDate() {
        return empty($this->start_date) && empty($this->end_date);
    }

    public function getPosition() {
        $data['isTravel'] = $this->isTravel();
        $data['isCar'] = $this->isDrive();
        $data['isPlane'] = $this->isFlight();
        $data['isTrain'] = $this->isTrainride();
        $data['isHotel'] = $this->isAccommodation();
        $data['isCarrental'] = $this->isCarrental();
        $data['isEvent'] = $this->isEvent();
        $data['isWaypoint'] = $this->isWaypoint();
        $data['isShip'] = $this->isShip();
        $data['isWithoutDate'] = $this->isWithoutDate();

        $data['data'] = $this->get_fields();

        return $data;
    }

    public function createPopup($dateFormatter, $timeFormatter, $datetimeFormatter, $from, $to, $loc_prefix = '<br/>', $loc_suffix = '<br/>') {
        $start = null;
        if (!is_null($this->start_date) && !is_null($this->start_time)) {
            $d = new \DateTime($this->start_date . ' ' . $this->start_time);
            $start = $datetimeFormatter->format($d);
        } else if (!is_null($this->start_date)) {
            $d = new \DateTime($this->start_date);
            $start = $dateFormatter->format($d);
        } else if (!is_null($this->start_time)) {
            $d = new \DateTime($this->start_time);
            $start = $timeFormatter->format($d);
        }

        $end = null;
        if (!is_null($this->end_date) && !is_null($this->end_time)) {
            $d = new \DateTime($this->end_date . ' ' . $this->end_time);
            $end = $datetimeFormatter->format($d);
        } else if (!is_null($this->end_date)) {
            $d = new \DateTime($this->end_date);
            $end = $dateFormatter->format($d);
        } else if (!is_null($this->end_time)) {
            $d = new \DateTime($this->end_time);
            $end = $timeFormatter->format($d);
        }

        // same day but different end time, so remove day format 
        if (!is_null($start) && !is_null($end) && strcmp($this->start_date ?? '', $this->end_date ?? '') === 0 && strcmp($this->start_time ?? '', $this->end_time ?? '') !== 0) {
            if (!is_null($this->end_time)) {
                $d = new \DateTime($this->end_time);
                $end = $timeFormatter->format($d);
            } else {
                $end = null;
            }
        }

        $start_address = null;
        $start_link = "<a href=\"geo:{$this->start_lat},{$this->start_lng}\" class=\"geo-link start_address\" data-lat=\"{$this->start_lat}\" data-lng=\"{$this->start_lng}\">";
        if (!is_null($this->start_address)) {
            $start_address = "{$start_link}{$this->start_address}</a>{$loc_suffix}";
        } elseif (!is_null($this->start_lat) && !is_null($this->start_lat)) {
            $start_address = " {$start_link}" . Utility::getFontAwesomeIcon('fas fa-location-dot') . "</a>{$loc_suffix}";
        }

        $end_address = null;
        $end_link = "<a href=\"geo:{$this->end_lat},{$this->end_lng}\" class=\"geo-link end_address\" data-lat=\"{$this->end_lat}\" data-lng=\"{$this->end_lng}\">";
        if (!is_null($this->end_address)) {
            $end_address = "{$end_link}{$this->end_address}</a>{$loc_suffix}";
        } elseif (!is_null($this->end_lat) && !is_null($this->end_lng)) {
            $start_address = " {$end_link}" . Utility::getFontAwesomeIcon('fas fa-location-dot') . "</a>{$loc_suffix}";
        }

        // same start and end date? hide end date
        if (strcmp($start ?? '', $end ?? '') === 0) {
            $end = null;
        }

        $start_sep = !is_null($start) && !is_null($start_address) ? $loc_prefix : "";
        $start1 = "{$start}{$start_sep}{$start_address}";

        $end_sep = !is_null($end) && !is_null($end_address) ? $loc_prefix : "";
        $end1 = "{$end}{$end_sep}{$end_address}";

        $popup = ""; //"<h4>{$this->name}</h4>";
        if (!empty($start1) && !empty($end1) && strcmp($start1 ?? '', $end1 ?? '') !== 0) {
            if (!empty($end)) {
                $popup .= "{$from} ";
            }
            $popup .= "{$start1}<br/>";
            if (!empty($end)) {
                $popup .= "{$to} ";
            }
            $popup .= "{$end1}<br/>";
        } else if (!empty($start1)) {
            // there is only a start date or start and end date are the same
            $popup .= "{$start1}<br/>";
        }
        if (empty($start1) && !empty($end1)) {
            // end date without start date
            $popup .= "{$end1}<br/>";
        }

        $this->popup = $popup;
    }

    public function getNotice() {
        return Utility::replaceLinks($this->notice);
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        if ($this->isWaypoint()) {
            return sprintf("%s, %s", $this->start_lat, $this->start_lng);
        }
        return $this->name;
    }

    public function getParentID() {
        return $this->trip;
    }

    public function get_thumbnail($size = 'small') {
        if (!empty($this->image)) {
            $file_extension = pathinfo($this->image, PATHINFO_EXTENSION);
            $file_wo_extension = pathinfo($this->image, PATHINFO_FILENAME);
            return $file_wo_extension . '-' . $size . '.' . $file_extension;
        }
        return null;
    }

    public function get_image() {
        if (!empty($this->image)) {
            return $this->image;
        }
        return null;
    }
}
