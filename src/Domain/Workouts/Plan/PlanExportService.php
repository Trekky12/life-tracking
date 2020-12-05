<?php

namespace App\Domain\Workouts\Plan;

use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Workouts\Exercise\ExerciseMapper;
use App\Domain\Workouts\Bodypart\BodypartMapper;
use App\Domain\Workouts\Muscle\MuscleMapper;
use App\Domain\Main\Translator;
use App\Domain\Workouts\Exercise\ExerciseService;
use App\Application\Payload\Payload;
use App\Domain\Settings\SettingsMapper;

class PlanExportService extends PlanService {

    private $exercise_service;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            PlanMapper $mapper,
            ExerciseMapper $exercise_mapper,
            BodypartMapper $bodypart_mapper,
            MuscleMapper $muscle_mapper,
            Translator $translation,
            SettingsMapper $settings_mapper,
            ExerciseService $exercise_service) {
        parent::__construct($logger, $user, $mapper, $exercise_mapper, $bodypart_mapper, $muscle_mapper, $translation, $settings_mapper);
        $this->exercise_service = $exercise_service;
    }

    public function export($hash) {
        $plan = $this->getFromHash($hash);

        if (!$this->isOwner($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        list($exercises, $muscles) = $this->getPlanExercises($plan->id);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setBottom(0.5);
        $sheet->getPageMargins()->setLeft(0.5);
        $sheet->getPageMargins()->setRight(0.5);


        // Plan Name
        $sheet->setCellValue('A1', $plan->name);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);

        $offset = 2;
        $idx = 0;
        foreach ($exercises as $ex) {

            $row_nr = ($idx + $offset);

            switch ($ex["type"]) {
                case "exercise":
                    $sets_count = $this->createExerciseRows($row_nr, $ex, $spreadsheet, $sheet);
                    $idx = $idx + 1 + $sets_count;
                    break;
                case "day":
                    $sheet->setCellValue('A' . ($row_nr + 1), $ex["notice"]);
                    $sheet->getStyle('A' . ($row_nr + 1))->getFont()->setBold(true)->setSize(16);
                    $sheet->mergeCells('A' . ($row_nr + 1) . ":L" . ($row_nr + 1));
                    $sheet->getStyle('A' . ($row_nr + 1) . ":L" . ($row_nr + 1))->applyFromArray(
                            ['borders' => [
                                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK],
                                ],
                            ]
                    );
                    $idx = $idx + 1;
                    break;
                case "superset":
                    $superset_row = $row_nr + 2;
                    $sheet->setCellValue('A' . ($superset_row), $this->translation->getTranslatedString('WORKOUTS_SUPERSET'));
                    $sheet->getStyle('A' . ($superset_row))->getFont()->setBold(true)->setSize(14);
                    $sheet->mergeCells('A' . ($superset_row) . ":L" . ($superset_row));

                    $idx = $idx + 2;
                    foreach ($ex["children"] as $child) {
                        $row_nr = ($idx + $offset);
                        $sets_count = $this->createExerciseRows($row_nr, $child, $spreadsheet, $sheet);
                        $idx = $idx + 1 + $sets_count;
                    }
                    $row_nr = ($idx + $offset);

                    $sheet->getStyle('A' . ($superset_row) . ":L" . ($row_nr))->applyFromArray(
                            ['borders' => [
                                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE],
                                ],
                            ]
                    );

                    break;
            }
        }


        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(10);
        $sheet->getColumnDimension('K')->setWidth(10);
        $sheet->getColumnDimension('L')->setWidth(10);


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

    private function createExerciseRows($row_nr, $exercise, $spreadsheet, $sheet) {
        // Exercise Name
        $sheet->setCellValue('A' . ($row_nr + 1), $exercise["exercise"]->name);
        $sheet->getStyle('A' . ($row_nr + 1))->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A' . ($row_nr + 1) . ":L" . ($row_nr + 1));
        $sheet->getStyle('A' . ($row_nr + 1) . ":L" . ($row_nr + 1))->applyFromArray(
                ['borders' => [
                        'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ],
                ]
        );

        if (!is_null($exercise["exercise"]->get_thumbnail())) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName($exercise["exercise"]->name);
            $drawing->setCoordinates('A' . ($row_nr + 2));
            $drawing->setPath($this->exercise_service->getFullImagePath() . "/" . $exercise["exercise"]->get_thumbnail());
            $drawing->setWorksheet($spreadsheet->getActiveSheet());
            $drawing->setHeight(20 * count($exercise["sets"]) - 2);
            $drawing->setOffsetY(2);
            $drawing->setOffsetX(10);
        }

        // Sets
        $sets_count = 0;
        if (array_key_exists("sets", $exercise) && is_array($exercise["sets"]) && !empty($exercise["sets"])) {
            $sets_count = count($exercise["sets"]);

            $sheet->setCellValue('B' . ($row_nr + 2), implode("\n", $exercise["set_description"]));
            $sheet->getStyle('B' . ($row_nr + 2))->getAlignment()->setWrapText(true);
            $sheet->getStyle('B' . ($row_nr + 2))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->mergeCells('B' . ($row_nr + 2) . ':B' . ($row_nr + 1 + $sets_count));

            // Columns
            $sheet->getStyle('B' . ($row_nr + 2) . ':L' . ($row_nr + 1 + $sets_count))->applyFromArray(
                    ['borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            ],
                        ],
                    ]
            );
        }


        return $sets_count;
    }

}
