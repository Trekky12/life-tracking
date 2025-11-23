<?php

namespace App\Domain\Timesheets\Sheet;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\SheetNotice\SheetNoticeMapper;
use App\Domain\Timesheets\NoticeField\NoticeFieldService;
use App\Domain\Main\Utility\DateUtility;
use App\Domain\Main\Utility\Utility;
use App\Domain\Main\Translator;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\ProjectCategoryBudget\ProjectCategoryBudgetService;

class SheetExportService extends Service {

    private $translation;
    private $project_service;
    private $settings;
    private $sheet_notice_mapper;
    private $notice_fields_service;
    private $project_category_budget_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        SheetMapper $mapper,
        ProjectService $project_service,
        SheetNoticeMapper $sheet_notice_mapper,
        NoticeFieldService $notice_fields_service,
        Settings $settings,
        Translator $translation,
        ProjectCategoryBudgetService $project_category_budget_service
    ) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->settings = $settings;
        $this->translation = $translation;
        $this->sheet_notice_mapper = $sheet_notice_mapper;
        $this->notice_fields_service = $notice_fields_service;
        $this->project_category_budget_service = $project_category_budget_service;
    }

    public function export($hash, $requestData) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }


        $type = array_key_exists("type", $requestData) ? Utility::filter_string_polyfill($requestData["type"]) : null;

        list($from, $to) = DateUtility::getDateRange($requestData);
        $categories = array_key_exists("categories", $requestData) ? filter_var_array($requestData["categories"], FILTER_SANITIZE_NUMBER_INT) : [];

        $invoiced = array_key_exists('invoiced', $requestData) && $requestData['invoiced'] !== '' ? intval(filter_var($requestData['invoiced'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $billed = array_key_exists('billed', $requestData) && $requestData['billed'] !== '' ? intval(filter_var($requestData['billed'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $payed = array_key_exists('payed', $requestData) && $requestData['payed'] !== '' ? intval(filter_var($requestData['payed'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $happened = array_key_exists('happened', $requestData) && $requestData['happened'] !== '' ? intval(filter_var($requestData['happened'], FILTER_SANITIZE_NUMBER_INT)) : null;

        $customer = array_key_exists('customer', $requestData) && $requestData['customer'] !== '' ? intval(filter_var($requestData['customer'], FILTER_SANITIZE_NUMBER_INT)) : null;

        $noticefields = array_key_exists("noticefields", $requestData) ? filter_var_array($requestData["noticefields"], FILTER_SANITIZE_NUMBER_INT) : [];

        $date_modified = array_key_exists('date_modified', $requestData) && $requestData['date_modified'] !== '' ? intval(filter_var($requestData['date_modified'], FILTER_SANITIZE_NUMBER_INT)) > 0 : true;

        if (strcmp($type ?? '', "word") == 0) {
            return $this->exportWord($project, $from, $to, $categories, $invoiced, $billed, $payed, $happened, $customer);
        }
        if (strcmp($type ?? '', "excel") == 0) {
            return $this->exportExcel($project, $from, $to, $categories, $invoiced, $billed, $payed, $happened, $customer);
        }

        if (strcmp($type ?? '', "html-overview") == 0) {
            return $this->exportHTMLOverview($project, $from, $to, $categories, $invoiced, $billed, $payed, $happened, $customer, $noticefields, $date_modified);
        }

        return $this->exportHTML($project, $from, $to, $categories, $invoiced, $billed, $payed, $happened, $customer);
    }

    private function exportExcel($project, $from, $to, $categories, $invoiced, $billed, $payed, $happened, $customer) {

        $include_empty_categories = true;
        // get Data
        $data = $this->getMapper()->getTableData($project->id, $from, $to, $categories, $include_empty_categories, $invoiced, $billed, $payed, $happened, $customer, 1, 'ASC', null);
        //$rendered_data = $this->renderTableRows($project, $data);
        //$totalSeconds = $this->mapper->tableSum($project->id, $from, $to);

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];
        $fmtDate = new \IntlDateFormatter($language);
        $fmtDate->setPattern($dateFormatPHP["date"]);

        $fromDate = new \DateTime($from ?? '');
        $toDate = new \DateTime($to ?? '');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($fmtDate->format($fromDate) . " " . $this->translation->getTranslatedString("TO") . " " . $fmtDate->format($toDate));

        // Project Name
        $sheet->setCellValue('A1', $project->name);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->mergeCells("A1:M1");

        // Range
        $sheet->setCellValue('A2', $fmtDate->format($fromDate) . " " . $this->translation->getTranslatedString("TO") . " " . $fmtDate->format($toDate));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells("A2:M2");

        // Table Header
        $sheet->setCellValue('A4', $this->translation->getTranslatedString("DATE"));
        $sheet->setCellValue('B4', $project->is_day_based ? $this->translation->getTranslatedString("TIMESHEETS_COME_DAY_BASED") : $this->translation->getTranslatedString("TIMESHEETS_COME_PROJECT_BASED"));
        $sheet->setCellValue('C4', $project->is_day_based ? $this->translation->getTranslatedString("TIMESHEETS_LEAVE_DAY_BASED") : $this->translation->getTranslatedString("TIMESHEETS_LEAVE_PROJECT_BASED"));

        if ($project->has_duration_modifications > 0) {
            $sheet->setCellValue('D4', $this->translation->getTranslatedString("DIFFERENCE"));
            $sheet->setCellValue('E4', $this->translation->getTranslatedString("TIMESHEETS_DIFFERENCE_CALCULATED_EXCEL"));
            $sheet->setCellValue('F4', $this->translation->getTranslatedString("TIMESHEETS_DATE_MODIFIED_EXCEL"));
            $sheet->setCellValue('G4', $this->translation->getTranslatedString("TIMESHEETS_START_MODIFIED_EXCEL"));
        } else {
            $sheet->setCellValue('E4', $this->translation->getTranslatedString("DIFFERENCE"));
        }

        $customerDescription = $project->customers_name_singular ? $project->customers_name_singular : $this->translation->getTranslatedString("TIMESHEETS_CUSTOMER");
        $sheet->setCellValue('H4', $customerDescription);
        $sheet->setCellValue('I4', $this->translation->getTranslatedString("CATEGORIES"));

        $sheet->setCellValue('J4', $this->translation->getTranslatedString("TIMESHEETS_HAPPENED"));
        $sheet->setCellValue('K4', $this->translation->getTranslatedString("TIMESHEETS_INVOICED"));
        $sheet->setCellValue('L4', $this->translation->getTranslatedString("TIMESHEETS_BILLED"));
        $sheet->setCellValue('M4', $this->translation->getTranslatedString("TIMESHEETS_PAYED"));

        $sheet->getStyle('A4:M4')->applyFromArray(
            [
                'borders' => [
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                ],
            ]
        );
        $sheet->getStyle('A4:M4')->getFont()->setBold(true);
        $sheet->getStyle('A4:M4')->getAlignment()->setWrapText(true)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

        $excelTime = "[$-F400]h:mm:ss AM/PM";
        $excelDate = $dateFormatPHP['date'];
        $excelDateTime = $dateFormatPHP['datetime'];
        $excelTimeDuration = "[hh]:mm:ss";

        // Table Data
        $offset = 4;
        $idx = 0;
        foreach ($data as $timesheet) {

            $start = $timesheet->getStartDateTime();
            $end = $timesheet->getEndDateTime();

            $row = ($idx + 1 + $offset);

            // only show time on end date when start date and end date are on the same day
            if (!is_null($timesheet->start) && !is_null($timesheet->end) && $timesheet->getStartDateTime()->format('Y-m-d') == $timesheet->getEndDateTime()->format('Y-m-d')) {
                $sheet->setCellValue('C' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($end));
                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode($excelTime);
            } elseif (!is_null($timesheet->end)) {
                $sheet->setCellValue('C' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($end));
                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode($excelDateTime);
            }

            if (!is_null($timesheet->start)) {
                $sheet->setCellValue('A' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($start->format('Y-m-d')));

                $sheet->setCellValue('B' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($start));
                $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode($excelTime);
            } elseif (!is_null($timesheet->end)) {
                $sheet->setCellValue('A' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($end->format('Y-m-d')));

                $sheet->setCellValue('C' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($end));
                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode($excelTime);
            }
            $sheet->getStyle('A' . $row)->getNumberFormat()->setFormatCode($excelDate);

            if (!is_null($timesheet->start) && !is_null($timesheet->end)) {

                if ($project->has_duration_modifications > 0) {
                    $sum = DateUtility::splitDateInterval($timesheet->duration_modified);
                    $sheet->setCellValue('D' . $row, $sum);
                }
                $sheet->setCellValue('E' . $row, "=C" . $row . "-B" . $row);
                $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode($excelTimeDuration);
            }

            if (!is_null($timesheet->start_modified)) {
                $start_modified = new \DateTime($timesheet->start_modified);

                $sheet->setCellValue('F' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($start_modified->format('Y-m-d')));
                $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode($excelDate);

                $sheet->setCellValue('G' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($start_modified));
                $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode($excelTime);
            }

            if (!is_null($timesheet->customerName)) {
                $sheet->setCellValue('H' . $row, $timesheet->customerName);
            }

            if (!is_null($timesheet->categories)) {
                $sheet->setCellValue('I' . $row, $timesheet->categories);
            }

            /* $notice = $this->sheet_notice_mapper->getNotice($timesheet->id);
                    if (!is_null($notice)) {
                    $sheet->setCellValue('G' . $row, htmlspecialchars_decode($notice->getNotice()));
                    $sheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);
                    } */

            $sheet->setCellValue('J' . $row, $timesheet->is_happened == 1 ? "x" : "");
            $sheet->setCellValue('K' . $row, $timesheet->is_invoiced == 1 ? "x" : "");
            $sheet->setCellValue('L' . $row, $timesheet->is_billed == 1 ? "x" : "");
            $sheet->setCellValue('M' . $row, $timesheet->is_payed == 1 ? "x" : "");

            $sheet->getStyle('A' . $row . ':M' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

            $idx++;
        }

        // Table Footer
        $firstRow = (1 + $offset);
        $lastRow = $firstRow + $idx - 1;
        $sumRow =  $firstRow + $idx + 1;

        // hide empty between lastRow and footer
        $sheet->getRowDimension($lastRow + 1)->setVisible(false);

        if ($project->has_duration_modifications > 0) {
            $totalSecondsModified = $this->getMapper()->tableSum($project->id, $from, $to, $categories, $include_empty_categories, $invoiced, $billed, $payed, $happened, $customer, "%", "t.duration_modified");
            $sum = DateUtility::splitDateInterval($totalSecondsModified);

            $sheet->setCellValue('D' . $sumRow, $sum);
        }

        $sheet->setCellValue('E' . $sumRow, "=SUM(E" . $firstRow . ":E" . $lastRow . ")");
        $sheet->getStyle('E' . $sumRow)->getNumberFormat()->setFormatCode($excelTimeDuration);

        $sheet->getStyle('A' . $sumRow . ':M' . $sumRow)->applyFromArray(
            [
                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                ],
            ]
        );

        // auto width
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);
        $sheet->getColumnDimension('M')->setAutoSize(true);

        $sheet->setAutoFilter("A" . $offset . ":M" . $lastRow);

        // sheet protection
        /*$protection = $sheet->getProtection();
        $protection->setSheet(true);
        $protection->setSort(false);
        $protection->setAutoFilter(false);

        $sheet->getStyle("A" . $offset . ":M" . $lastRow)
            ->getProtection()->setLocked(
                \PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED
            );
        $sheet->getStyle("A1:M2")
            ->getProtection()->setLocked(
                \PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED
            );
        */

        if ($project->has_duration_modifications > 0) {
            $sheet->getColumnDimension('D')->setVisible(true);
            $sheet->getColumnDimension('F')->setVisible(true);
            $sheet->getColumnDimension('G')->setVisible(true);
        } else {
            $sheet->getColumnDimension('D')->setVisible(false);
            $sheet->getColumnDimension('F')->setVisible(false);
            $sheet->getColumnDimension('G')->setVisible(false);
        }

        $sheet->setSelectedCell('A' . $sumRow + 1);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        /**
         * PSR-7 not supported
         * @see https://github.com/PHPOffice/PhpSpreadsheet/issues/28
         * so a temporary file in a specific directory is generated and later deleted 
         */
        $path = __DIR__ . '/../../../../files/tmp/';
        $excelFileName = @tempnam($path, 'phpxltmp');
        $writer->save($excelFileName);

        /**
         * We should use a Stream Object but then deleting is not possible, so instead use file_get_contents
         * @see https://gist.github.com/odan/a7a1eb3c876c9c5b2ffd2db55f29fdb8
         * @see https://odan.github.io/2017/12/16/creating-and-downloading-excel-files-with-slim.html
         * @see https://stackoverflow.com/a/51675156
         */
        /*
                    * $stream = fopen($excelFileName, 'r+');
                    * $response->withBody(new \Slim\Http\Stream($stream))->...
                    */
        $body = file_get_contents($excelFileName);
        unlink($excelFileName);

        return new Payload(Payload::$RESULT_EXCEL, $body);
    }

    private function exportWord($project, $from, $to, $categories, $invoiced, $billed, $payed, $happened, $customer) {

        $include_empty_categories = true;
        // get Data
        $data = $this->getMapper()->getTableData($project->id, $from, $to, $categories, $include_empty_categories, $invoiced, $billed, $payed, $happened, $customer, 1, 'ASC', null);

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];
        $fmtDate = new \IntlDateFormatter($language);
        $fmtDate->setPattern($dateFormatPHP["date"]);

        $fromDate = new \DateTime($from ?? '');
        $toDate = new \DateTime($to ?? '');

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->addTitleStyle(1, array('name' => 'Arial', 'size' => 16, 'color' => '000000'));

        foreach ($data as $timesheet) {

            $section = $phpWord->addSection();

            list($date, $start, $end) = $timesheet->getDateStartEnd($language, $dateFormatPHP['date'], $dateFormatPHP['datetime'], $dateFormatPHP['time']);

            $section->addTitle(sprintf("%s %s - %s", $date, $start, $end), 1);

            if (!is_null($timesheet->start) && !is_null($timesheet->end)) {

                $time_duration_real = DateUtility::splitDateInterval($timesheet->duration);

                if ($project->has_duration_modifications > 0) {
                    $time_duration_mod = DateUtility::splitDateInterval($timesheet->duration_modified);
                    $section->addText(sprintf("%s: %s (%s)", $this->translation->getTranslatedString("DIFFERENCE"), $time_duration_mod, $time_duration_real));
                } else {
                    $section->addText(sprintf("%s: %s", $this->translation->getTranslatedString("DIFFERENCE"), $time_duration_real));
                }
            }

            if (!is_null($timesheet->customerName)) {
                $customerDescription = $project->customers_name_singular ? $project->customers_name_singular : $this->translation->getTranslatedString("TIMESHEETS_CUSTOMER");
                $section->addText(sprintf("%s: %s", $customerDescription, $timesheet->customerName));
            }

            if (!is_null($timesheet->categories)) {
                $section->addText(sprintf("%s: %s", $this->translation->getTranslatedString("CATEGORIES"), $timesheet->categories));
            }

            /* $notice = $this->sheet_notice_mapper->getNotice($timesheet->id);
                        if (!is_null($notice)) {
                        
                        $section->addText($this->translation->getTranslatedString("NOTICE") . ":");
                        
                        $notice = explode("\n", $notice->getNotice());
                        
                        foreach ($notice as $line) {
                        $section->addText(htmlspecialchars(htmlspecialchars_decode($line)));
                        }
                        } */


            if (next($data) == true) {
                $section->addPageBreak();
            }
        }


        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');

        /**
         * PSR-7 not supported
         * @see https://github.com/PHPOffice/PhpSpreadsheet/issues/28
         * so a temporary file in a specific directory is generated and later deleted 
         */
        $path = __DIR__ . '/../../../../files/tmp/';
        $wordFileName = @tempnam($path, 'phpwordtmp');
        $writer->save($wordFileName);

        /**
         * We should use a Stream Object but then deleting is not possible, so instead use file_get_contents
         * @see https://gist.github.com/odan/a7a1eb3c876c9c5b2ffd2db55f29fdb8
         * @see https://odan.github.io/2017/12/16/creating-and-downloading-excel-files-with-slim.html
         * @see https://stackoverflow.com/a/51675156
         */
        /*
                    * $stream = fopen($excelFileName, 'r+');
                    * $response->withBody(new \Slim\Http\Stream($stream))->...
                    */
        $body = file_get_contents($wordFileName);
        unlink($wordFileName);

        return new Payload(Payload::$RESULT_WORD, $body);
    }

    private function exportHTML($project, $from, $to, $categories, $invoiced, $billed, $payed, $happened, $customer) {

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];
        $fmtDate = new \IntlDateFormatter($language);
        $fmtDate->setPattern($dateFormatPHP["date"]);

        $include_empty_categories = true;

        // get Data
        $data = $this->getMapper()->getTableData($project->id, $from, $to, $categories, $include_empty_categories, $invoiced, $billed, $payed, $happened, $customer, 1, 'ASC', null);

        $sheets = [];

        foreach ($data as $timesheet) {

            $sheet = [
                "id" => $timesheet->id
            ];
            list($date, $start, $end) = $timesheet->getDateStartEnd($language, $dateFormatPHP['date'], $dateFormatPHP['datetime'], $dateFormatPHP['time']);

            $sheet["title"] = sprintf("%s %s - %s", $date, $start, $end);

            if (!is_null($timesheet->start) && !is_null($timesheet->end)) {

                $time_duration_real = DateUtility::splitDateInterval($timesheet->duration);

                if ($project->has_duration_modifications > 0) {
                    $time_duration_mod = DateUtility::splitDateInterval($timesheet->duration_modified);

                    $sheet["time"] = sprintf("%s (%s)", $time_duration_mod, $time_duration_real);
                } else {
                    $sheet["time"] = $time_duration_real;
                }
            }

            if (!is_null($timesheet->categories)) {
                $sheet["categories"] = $timesheet->categories;
            }
            if (!is_null($timesheet->customerName)) {
                $sheet["customer"] = $timesheet->customerName;
            }
            $sheets[] = $sheet;
        }

        $response = [
            "project" => $project,
            "hasTimesheetNotice" => true,
            "sheets" => $sheets,
            "fields" => $this->notice_fields_service->getNoticeFields($project->id, 'sheet')
        ];

        return new Payload(Payload::$RESULT_HTML, $response);
    }

    private function exportHTMLOverview($project, $from, $to, $categories, $invoiced, $billed, $payed, $happened, $customer, $noticefields = [], $date_modified = true) {

        $dateFormatSQL = $this->settings->getAppSettings()['i18n']['dateformatSQL'];
        $include_empty_categories = false;
        $data = $this->getMapper()->getOverview($project->id, $from, $to, $categories, $include_empty_categories, $invoiced, $billed, $payed, $happened, $customer, $dateFormatSQL["dateTimesheetsExport"], $date_modified);

        $fields = $this->notice_fields_service->getNoticeFields($project->id, 'customer');

        // filter all not selected fields
        $selected_fields = array_filter($fields, function ($field) use ($noticefields) {
            return in_array($field->id, $noticefields);
        });

        // Order by noticefields order
        $sorted_fields = array_map(function ($id) use ($selected_fields) {
            return $selected_fields[$id];
        }, $noticefields);

        $sum = array_sum(array_column($data, 'count'));

        $response = [
            "project" => $project,
            "hasTimesheetNotice" => true,
            "data" => $data,
            "fields" => $sorted_fields,
            "from" => $from,
            "to" => $to,
            "sum" => $sum
        ];

        return new Payload(Payload::$RESULT_HTML, $response);
    }
}
