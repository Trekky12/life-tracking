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

        $exercises = $this->exercise_mapper->getAll();
        $bodyparts = $this->bodypart_mapper->getAll();
        $muscles = $this->muscle_mapper->getAll();
        $selected_exercises = $this->mapper->getExercises($plan->id);

        $exercises_print = [];
        foreach ($selected_exercises as $se) {
            $exercise = $exercises[$se["exercise"]];

            $sets = array_map(function($set) use ($exercise) {
                $description = [];
                if ($exercise->isCategoryReps() || $exercise->isCategoryRepsWeight()) {
                    $description[] = sprintf("%s %s", $set["repeats"], $this->translation->getTranslatedString("WORKOUTS_REPEATS"));
                }
                if ($exercise->isCategoryRepsWeight()) {
                    $description[] = sprintf("%s %s", $set["weight"], $this->translation->getTranslatedString("WORKOUTS_KG"));
                }
                if ($exercise->isCategoryTime() || $exercise->isCategoryDistanceTime()) {
                    $description[] = sprintf("%s %s", $set["time"], $this->translation->getTranslatedString("WORKOUTS_MINUTES"));
                }
                if ($exercise->isCategoryDistanceTime()) {
                    $description[] = sprintf("%s %s", $set["distance"], $this->translation->getTranslatedString("WORKOUTS_KM"));
                }
                return implode(', ', $description);
            }, $se["sets"]);

            $exercises_print[] = ["exercise" => $exercise,
                "mainBodyPart" => array_key_exists($exercise->mainBodyPart, $bodyparts) ? $bodyparts[$exercise->mainBodyPart]->name : '',
                "mainMuscle" => array_key_exists($exercise->mainMuscle, $muscles) ? $muscles[$exercise->mainMuscle]->name : '',
                "sets" => $sets
            ];
        }

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
        foreach ($exercises_print as $ex) {

            // Exercise Name
            $sheet->setCellValue('A' . ($idx + 1 + $offset), $ex["exercise"]->name);
            $sheet->getStyle('A' . ($idx + 1 + $offset))->getFont()->setBold(true)->setSize(14);
            $sheet->mergeCells('A' . ($idx + 1 + $offset) . ":L" . ($idx + 1 + $offset));
            $sheet->getStyle('A' . ($idx + 1 + $offset) . ":L" . ($idx + 1 + $offset))->applyFromArray(
                    ['borders' => [
                            'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        ],
                    ]
            );

            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName($ex["exercise"]->name);
            $drawing->setCoordinates('A' . ($idx + 2 + $offset));
            $drawing->setPath($this->exercise_service->getFullImagePath() . "/" . $ex["exercise"]->get_thumbnail());
            $drawing->setWorksheet($spreadsheet->getActiveSheet());
            $drawing->setHeight(20 * count($ex["sets"]) - 2);
            $drawing->setOffsetY(2);

            // Sets
            $sheet->setCellValue('B' . ($idx + 2 + $offset), implode("\n", $ex["sets"]));
            $sheet->getStyle('B' . ($idx + 2 + $offset))->getAlignment()->setWrapText(true);
            $sheet->getStyle('B' . ($idx + 2))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->mergeCells('B' . ($idx + 2 + $offset) . ':B' . ($idx + 1 + $offset + count($ex["sets"])));

            // Columns
            $sheet->getStyle('B' . ($idx + 2 + $offset) . ':L' . ($idx + 1 + $offset + count($ex["sets"])))->applyFromArray(
                    ['borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            ],
                        ],
                    ]
            );

            $idx = $idx + 1 + count($ex["sets"]);
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

}
