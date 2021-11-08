<?php

namespace App\Domain\Timesheets\SheetNotice;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Sheet\SheetService;
use App\Domain\Timesheets\Sheet\SheetMapper;
use App\Domain\Base\Settings;
use App\Application\Payload\Payload;

class SheetNoticeService extends Service {

    protected $project_service;
    protected $sheet_service;
    protected $user_service;
    protected $settings;
    protected $sheet_mapper;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            SheetNoticeMapper $mapper,
            ProjectService $project_service,
            SheetService $sheet_service,
            SheetMapper $sheet_mapper,
            Settings $settings) {
        parent::__construct($logger, $user);

        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->sheet_service = $sheet_service;
        $this->sheet_mapper = $sheet_mapper;
        $this->settings = $settings;
    }

    public function edit($hash, $sheet_id) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $sheet = $this->sheet_service->getEntry($sheet_id);

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];
        $fmtDate = new \IntlDateFormatter($language, NULL, NULL);
        $fmtDate->setPattern($dateFormatPHP["date"]);

        list($date, $start, $end) = $sheet->getDateStartEnd($language, $dateFormatPHP['date'], $dateFormatPHP['datetime'], $dateFormatPHP['time']);
        $sheet_title = sprintf("%s %s - %s", $date, $start, $end);

        $sheet_categories = $this->sheet_mapper->getCategoriesWithNamesFromSheet($sheet_id);

        $response_data = [
            'sheet' => $sheet,
            'sheet_categories' => $sheet_categories,
            'sheet_title' => $sheet_title,
            'project' => $project,
            'hasTimesheetNotice' => true
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function getData($hash, $sheet_id) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->mapper->getNotice($sheet_id);

        $response_data = [
            'entry' => $entry
        ];

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

}
