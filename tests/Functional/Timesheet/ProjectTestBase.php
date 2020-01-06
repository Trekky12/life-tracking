<?php

namespace Tests\Functional\Timesheet;

use Tests\Functional\Base\BaseTestCase;

class ProjectTestBase extends BaseTestCase {

    protected $uri_overview = "/timesheets/projects/";
    protected $uri_edit = "/timesheets/projects/edit/";
    protected $uri_save = "/timesheets/projects/save/";
    protected $uri_delete = "/timesheets/projects/delete/";
    protected $uri_view = "/timesheets/HASH/view/";
    protected $uri_child_edit = "/timesheets/HASH/sheets/edit/";
    protected $uri_child_save = "/timesheets/HASH/sheets/save/";
    protected $uri_child_delete = "/timesheets/HASH/sheets/delete/";
    
    private $uri_sheets_fast = "/timesheets/HASH/fast/";
    private $uri_sheets_fast_checkin = "/timesheets/HASH/fast/checkin";
    private $uri_sheets_fast_checkout = "/timesheets/HASH/fast/checkout";
    private $uri_sheets_export = "/timesheets/HASH/export";

    protected function getParent($body, $name) {
        $matches = [];
        $re = '/<tr>\s*<td><a href="\/timesheets\/(?<hash>.*)\/view\/">' . preg_quote($name) . '<\/a><\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>.*)"><span class="fa fa-pencil-square-o fa-lg"><\/span><\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>.*)" class="btn-delete"><span class="fa fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getChild($body, $data, $hash) {

        $language = $this->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->getAppSettings()['i18n']['dateformatPHP'];

        $fmtDate = new \IntlDateFormatter($language, NULL, NULL);
        $fmtDate->setPattern($dateFormatPHP['date']);

        $fmtDateTime = new \IntlDateFormatter($language, NULL, NULL);
        $fmtDateTime->setPattern($dateFormatPHP['datetimeShort']);

        $fmtTime = new \IntlDateFormatter($language, NULL, NULL);
        $fmtTime->setPattern($dateFormatPHP['time']);

        $start = new \DateTime($data["start"]);
        $end = new \DateTime($data["end"]);

        $dateTD = !is_null($data["start"]) ? $fmtDate->format($start) : !is_null($data["end"]) ? $fmtDate->format($end) : "";
        $startTD = !is_null($data["start"]) ? $fmtTime->format($start) : "";
        $endTD = !is_null($data["end"]) ? $fmtTime->format($end) : "";

        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($dateTD) . '<\/td>\s*<td>' . preg_quote($startTD) . '<\/td>\s*<td>' . preg_quote($endTD) . '<\/td>\s*<td>' . preg_quote($data["diff"]) . '<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->getURIChildEdit($hash)) . '(?<id_edit>.*)"><span class="fa fa-pencil-square-o fa-lg"><\/span><\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->getURIChildDelete($hash)) . '(?<id_delete>.*)" class="btn-delete"><span class="fa fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }


    protected function getURISheetsFast($hash) {
        return str_replace("HASH", $hash, $this->uri_sheets_fast);
    }

    protected function getURISheetsFastCheckin($hash) {
        return str_replace("HASH", $hash, $this->uri_sheets_fast_checkin);
    }

    protected function getURISheetsFastCheckout($hash) {
        return str_replace("HASH", $hash, $this->uri_sheets_fast_checkout);
    }

    protected function getURISheetsExport($hash) {
        return str_replace("HASH", $hash, $this->uri_sheets_export);
    }

}
