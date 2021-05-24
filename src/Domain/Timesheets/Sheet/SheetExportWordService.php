<?php

namespace App\Domain\Timesheets\Sheet;

use Psr\Log\LoggerInterface;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\ProjectCategory\ProjectCategoryService;
use App\Domain\Main\Utility\DateUtility;
use App\Domain\Main\Translator;
use App\Application\Payload\Payload;

class SheetExportWordService extends SheetService {

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

            if (!is_null($timesheet->notice)) {

                $section->addText($this->translation->getTranslatedString("NOTICE") . ":");

                $notice = explode("\n", $timesheet->notice);

                foreach ($notice as $line) {
                    $section->addText(htmlspecialchars(htmlspecialchars_decode($line)));
                }
            }


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

        return new Payload(Payload::$RESULT_RAW, $body);
    }

}
