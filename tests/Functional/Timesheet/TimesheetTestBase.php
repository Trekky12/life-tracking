<?php

namespace Tests\Functional\Timesheet;

use Tests\Functional\Base\BaseTestCase;

class TimesheetTestBase extends BaseTestCase {

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
    private $uri_sheets_export_view = "/timesheets/HASH/export/";
    private $uri_sheets_export = "/timesheets/HASH/export/download";

    protected $uri_notice_edit = "/timesheets/HASH/sheets/notice/(?<id_notice>[0-9]*)/edit/";
    protected $uri_notice_customer_edit = "/timesheets/HASH/customers/notice/(?<id_notice>[0-9]*)/edit/";

    protected function getParent($body, $name) {
        $matches = [];
        $re = '/<tr>\s*<td>\s*<a href="\/timesheets\/(?<hash>.*)\/view\/">' . preg_quote($name ?? '') . '<\/a>\s*<\/td>\s*(<td(.*)?>.*?|\s*<\/td>\s*)*<td>\s*<a href="\/timesheets\/([0-9a-zA-Z]+)\/noticepassword\/\">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="\/timesheets\/([0-9a-zA-Z]+)\/noticefields\/\">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>[0-9]*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getChild($body, $data, $hash) {

        $language = $this->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->getAppSettings()['i18n']['dateformatPHP'];

        $fmtDate = new \IntlDateFormatter($language);
        $fmtDate->setPattern($dateFormatPHP['date']);

        $fmtDateTime = new \IntlDateFormatter($language);
        $fmtDateTime->setPattern($dateFormatPHP['datetimeShort']);

        $fmtTime = new \IntlDateFormatter($language);
        $fmtTime->setPattern($dateFormatPHP['time']);

        $start = new \DateTime($data["start"] ?? '');
        $end = new \DateTime($data["end"] ?? '');

        $dateTD = !is_null($data["start"]) ? $fmtDate->format($start) : (!is_null($data["end"]) ? $fmtDate->format($end) : "");
        $startTD = !is_null($data["start"]) ? $fmtTime->format($start) : "";
        $endTD = !is_null($data["end"]) ? $fmtTime->format($end) : "";

        $matches = [];
        $re = '/<tr data-invoiced="0" data-billed="0" data-payed="0" data-happened="0">\s*<td><input type="checkbox" name="check_row" data-id="(?<id>[0-9]*)"><\/td>\s*<td>' . preg_quote($dateTD ?? '') . '<\/td>\s*<td>' . preg_quote($startTD ?? '') . '<\/td>\s*<td>' . preg_quote($endTD ?? '') . '<\/td>\s*<td>' . preg_quote($data["diff"] ?? '') . '<\/td>\s*<td>\s*<\/td>\s*<td>\s*<\/td>\s*<td>\s*<\/td>\s*<td>\s*<\/td>\s*<td>\s*<\/td>\s*<td>\s*<\/td>\s*<td><a href="' . str_replace('/', "\/", $this->getURINoticeEdit($hash)) . '">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->getURIChildEdit($hash)) . '(?<id_edit>[0-9]*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->getURIChildDelete($hash)) . '(?<id_delete>[0-9]*)" data-warning="" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
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

    protected function getURISheetsExportView($hash) {
        return str_replace("HASH", $hash, $this->uri_sheets_export_view);
    }

    protected function getURISheetsExport($hash) {
        return str_replace("HASH", $hash, $this->uri_sheets_export);
    }


    protected function getURINoticeEdit($hash) {
        return str_replace("HASH", $hash, $this->uri_notice_edit);
    }

    protected function getURICustomerNoticeEdit($hash) {
        return str_replace("HASH", $hash, $this->uri_notice_customer_edit);
    }

    protected function getCustomerElementInTable($body, $data, $hash) {
        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($data["name"] ?? '') . '<\/td>\s*<td>.*?<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->getURICustomerNoticeEdit($hash)) . '">\s*(.*)\s*<\/a>\s*<\/td>\s*\s*<td>\s*<a href="' . str_replace('/', "\/", $this->getURIChildEdit($hash)) . '(?<id_edit>[0-9]*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->getURIChildDelete($hash)) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getNoticeFieldElementInTable($body, $data, $hash) {
        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($data["name"] ?? '') . '<\/td>\s*<td>.*?<\/td>\s*<td>\s*(.*)\s*<\/td>\s*<td>.*?<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->getURIChildEdit($hash)) . '(?<id_edit>[0-9]*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->getURIChildDelete($hash)) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getRequirementElementInTable($body, $data, $hash) {
        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($data["name"] ?? '') . '<\/td>\s*<td>.*?<\/td>\s*<td>\s*(.*)\s*<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->getURIChildEdit($hash)) . '(?<id_edit>[0-9]*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->getURIChildDelete($hash)) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getReminderInTable($body, $data, $hash) {
        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($data["name"] ?? '') . '<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->getURIChildEdit($hash)) . '(?<id_edit>[0-9]*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->getURIChildDelete($hash)) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }
}
