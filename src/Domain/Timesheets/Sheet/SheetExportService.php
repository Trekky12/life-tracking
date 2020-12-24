<?php

namespace App\Domain\Timesheets\Sheet;

use Psr\Log\LoggerInterface;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\ProjectCategory\ProjectCategoryService;
use App\Domain\Main\Translator;
use App\Application\Payload\Payload;

class SheetExportService extends SheetService {

    private $translation;

    public function __construct(LoggerInterface $logger, 
            CurrentUser $user, 
            SheetMapper $mapper, 
            ProjectService $project_service, 
            ProjectCategoryService $project_category_service, 
            UserService $user_service, 
            Settings $settings, 
            RouteParser $router, 
            Translator $translation) {
        parent::__construct($logger, $user, $mapper, $project_service, $project_category_service, $user_service, $settings, $router);
        $this->translation = $translation;
    }

    public function export($hash, $from, $to) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        // get Data
        $data = $this->mapper->getTableData($project->id, $from, $to, 0, 'ASC', null);
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
        $sheet->setCellValue('D4', $this->translation->getTranslatedString("DIFFERENCE"));
        $sheet->setCellValue('E4', $this->translation->getTranslatedString("NOTICE"));
        $sheet->setCellValue('F4', $this->translation->getTranslatedString("CATEGORIES"));
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
        $excelTimeDiff = "[hh]:mm:ss";

        // Table Data
        $offset = 4;
        $idx = 0;
        foreach ($data as $timesheet) {

            //list($date, $start, $end) = $timesheet->getDateStartEnd($language, $dateFormatPHP['date'], $dateFormatPHP['datetimeShort'], $dateFormatPHP['time']);
            //$diff = $this->helper->splitDateInterval($timesheet->diff);
            //$sheet->setCellValue('A' . ($idx + 1 + $offset), $date);
            //$sheet->setCellValue('B' . ($idx + 1 + $offset), $start);
            //$sheet->setCellValue('C' . ($idx + 1 + $offset), $end);
            //$sheet->setCellValue('D' . ($idx + 1 + $offset), $diff);

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
                $sheet->setCellValue('D' . $row, "=C" . $row . "-B" . $row);
                $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode($excelTimeDiff);
            }

            if (!is_null($timesheet->notice)) {
                $sheet->setCellValue('E' . $row, $timesheet->notice);
                $sheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            }

            if (!is_null($timesheet->categories)) {
                $sheet->setCellValue('F' . $row, $timesheet->categories);
            }

            $sheet->getStyle('A' . $row)->getNumberFormat()->setFormatCode($excelDate);
            $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

            $idx++;
        }

        // Table Footer
        $firstRow = (1 + $offset);
        $sumRow = ($idx + 1 + $offset);
        $sheet->setCellValue('D' . $sumRow, "=SUM(D" . $firstRow . ":D" . ($sumRow - 1) . ")");
        $sheet->getStyle('D' . $sumRow)->getNumberFormat()->setFormatCode($excelTimeDiff);

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

        return new Payload(Payload::$RESULT_RAW, $body);
    }

}
