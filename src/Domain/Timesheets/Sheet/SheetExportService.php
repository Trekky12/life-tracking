<?php

namespace App\Domain\Timesheets\Sheet;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\SheetNotice\SheetNoticeMapper;
use App\Domain\Main\Utility\DateUtility;
use App\Domain\Main\Translator;
use App\Application\Payload\Payload;

class SheetExportService extends Service {

    private $translation;
    private $project_service;
    private $settings;
    private $sheet_notice_mapper;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            SheetMapper $mapper,
            ProjectService $project_service,
            SheetNoticeMapper $sheet_notice_mapper,
            Settings $settings,
            Translator $translation) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->settings = $settings;
        $this->translation = $translation;
        $this->sheet_notice_mapper = $sheet_notice_mapper;
    }

    public function export($hash, $data) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        list($from, $to) = DateUtility::getDateRange($data);
        $categories = array_key_exists("categories", $data) ? filter_var_array($data["categories"], FILTER_SANITIZE_NUMBER_INT) : [];

        $type = array_key_exists("type", $data) ? filter_var($data["type"], FILTER_SANITIZE_STRING) : null;
        if (strcmp($type, "word") == 0) {
            return $this->exportWord($project, $from, $to, $categories);
        }
        if (strcmp($type, "excel") == 0) {
            return $this->exportExcel($project, $from, $to, $categories);
        }

        return $this->exportHTML($project, $from, $to, $categories);
    }

    private function exportExcel($project, $from, $to, $categories) {

        // get Data
        $data = $this->mapper->getTableData($project->id, $from, $to, $categories, 0, 'ASC', null);
        //$rendered_data = $this->renderTableRows($project, $data);
        //$totalSeconds = $this->mapper->tableSum($project->id, $from, $to);

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];
        $fmtDate = new \IntlDateFormatter($language, NULL, NULL);
        $fmtDate->setPattern($dateFormatPHP["date"]);

        $fromDate = new \DateTime($from);
        $toDate = new \DateTime($to);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($fmtDate->format($fromDate) . " " . $this->translation->getTranslatedString("TO") . " " . $fmtDate->format($toDate));

        // Project Name
        $sheet->setCellValue('A1', $project->name);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->mergeCells("A1:F1");

        // Range
        $sheet->setCellValue('A2', $fmtDate->format($fromDate) . " " . $this->translation->getTranslatedString("TO") . " " . $fmtDate->format($toDate));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells("A2:F2");

        // Table Header
        $sheet->setCellValue('A4', $this->translation->getTranslatedString("DATE"));
        $sheet->setCellValue('B4', $project->is_day_based ? $this->translation->getTranslatedString("TIMESHEETS_COME_DAY_BASED") : $this->translation->getTranslatedString("TIMESHEETS_COME_PROJECT_BASED"));
        $sheet->setCellValue('C4', $project->is_day_based ? $this->translation->getTranslatedString("TIMESHEETS_LEAVE_DAY_BASED") : $this->translation->getTranslatedString("TIMESHEETS_LEAVE_PROJECT_BASED"));

        if ($project->has_duration_modifications > 0) {
            $sheet->setCellValue('D4', $this->translation->getTranslatedString("DIFFERENCE"));
            $sheet->setCellValue('E4', $this->translation->getTranslatedString("DIFFERENCE_CALCULATED"));
        } else {
            $sheet->setCellValue('E4', $this->translation->getTranslatedString("DIFFERENCE"));
        }

        $sheet->setCellValue('F4', $this->translation->getTranslatedString("CATEGORIES"));
        //$sheet->setCellValue('G4', $this->translation->getTranslatedString("NOTICE"));
        $sheet->getStyle('A4:F4')->applyFromArray(
                ['borders' => [
                        'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ],
                ]
        );
        $sheet->getStyle('A4:F4')->getFont()->setBold(true);

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
                $sheet->setCellValue('A' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($start));

                $sheet->setCellValue('B' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($start));
                $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode($excelTime);
            } elseif (!is_null($timesheet->end)) {
                $sheet->setCellValue('A' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($end));

                $sheet->setCellValue('C' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($end));
                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode($excelTime);
            }

            if (!is_null($timesheet->start) && !is_null($timesheet->end)) {

                if ($project->has_duration_modifications > 0) {
                    $sum = DateUtility::splitDateInterval($timesheet->duration_modified);
                    $sheet->setCellValue('D' . $row, $sum);
                }
                $sheet->setCellValue('E' . $row, "=C" . $row . "-B" . $row);
                $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode($excelTimeDuration);
            }

            if (!is_null($timesheet->categories)) {
                $sheet->setCellValue('F' . $row, $timesheet->categories);
            }

            /* $notice = $this->sheet_notice_mapper->getNotice($timesheet->id);
              if (!is_null($notice)) {
              $sheet->setCellValue('G' . $row, htmlspecialchars_decode($notice->getNotice()));
              $sheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);
              } */

            $sheet->getStyle('A' . $row)->getNumberFormat()->setFormatCode($excelDate);
            $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

            $idx++;
        }

        // Table Footer
        $firstRow = (1 + $offset);
        $sumRow = ($idx + 1 + $offset);

        if ($project->has_duration_modifications > 0) {
            $totalSecondsModified = $this->mapper->tableSum($project->id, $from, $to, [], "%", "t.duration_modified");
            $sum = DateUtility::splitDateInterval($totalSecondsModified);

            $sheet->setCellValue('D' . $sumRow, $sum);
        }

        $sheet->setCellValue('E' . $sumRow, "=SUM(E" . $firstRow . ":E" . ($sumRow - 1) . ")");
        $sheet->getStyle('E' . $sumRow)->getNumberFormat()->setFormatCode($excelTimeDuration);

        $sheet->getStyle('A' . $sumRow . ':F' . $sumRow)->applyFromArray(
                ['borders' => [
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
        //$sheet->getColumnDimension('G')->setAutoSize(true);
        // sheet protection
        $sheet->getProtection()->setSheet(true);
        $sheet->getStyle("A" . $firstRow . ":C" . ($sumRow - 1))
                ->getProtection()->setLocked(
                \PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED
        );
        $sheet->getStyle("A1:F2")
                ->getProtection()->setLocked(
                \PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED
        );

        if ($project->has_duration_modifications > 0) {
            $sheet->getColumnDimension('D')->setVisible(true);
        } else {
            $sheet->getColumnDimension('D')->setVisible(false);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        /**
         * PSR-7 not supported
         * @see https://github.com/PHPOffice/PhpSpreadsheet/issues/28
         * so a temporary file in a specific directory is generated and later deleted 
         */
        $path = __DIR__ . '/../../../files/';
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

    private function exportWord($project, $from, $to, $categories) {
        // get Data
        $data = $this->mapper->getTableData($project->id, $from, $to, $categories, 0, 'ASC', null);

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];
        $fmtDate = new \IntlDateFormatter($language, NULL, NULL);
        $fmtDate->setPattern($dateFormatPHP["date"]);

        $fromDate = new \DateTime($from);
        $toDate = new \DateTime($to);

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
        $path = __DIR__ . '/../../../files/';
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

    private function exportHTML($project, $from, $to, $categories) {

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];
        $fmtDate = new \IntlDateFormatter($language, NULL, NULL);
        $fmtDate->setPattern($dateFormatPHP["date"]);

        // get Data
        $data = $this->mapper->getTableData($project->id, $from, $to, $categories, 0, 'ASC', null);

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
            $sheets[] = $sheet;
        }

        $response = [
            "project" => $project,
            "hasTimesheetNotice" => true,
            "sheets" => $sheets
        ];

        return new Payload(Payload::$RESULT_HTML, $response);
    }

}
