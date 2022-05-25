<?php

namespace App\Domain\Timesheets\SheetNotice;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Sheet\SheetService;
use App\Domain\Timesheets\Sheet\SheetMapper;
use App\Domain\Timesheets\NoticeField\NoticeFieldService;
use App\Domain\Base\Settings;
use App\Application\Payload\Payload;

class SheetNoticeService extends Service {

    protected $project_service;
    protected $sheet_service;
    protected $user_service;
    protected $settings;
    protected $sheet_mapper;
    protected $noticefield_service;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            SheetNoticeMapper $mapper,
            ProjectService $project_service,
            SheetService $sheet_service,
            SheetMapper $sheet_mapper,
            NoticeFieldService $noticefield_service,
            Settings $settings) {
        parent::__construct($logger, $user);

        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->sheet_service = $sheet_service;
        $this->sheet_mapper = $sheet_mapper;
        $this->noticefield_service = $noticefield_service;
        $this->settings = $settings;
    }

    public function edit($hash, $sheet_id) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->sheet_service->isChildOf($project->id, $sheet_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $sheet = $this->sheet_service->getEntry($sheet_id);

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];
        $fmtDate = new \IntlDateFormatter($language, NULL, NULL);
        $fmtDate->setPattern($dateFormatPHP["date"]);

        list($date, $start, $end) = $sheet->getDateStartEnd($language, $dateFormatPHP['date'], $dateFormatPHP['datetime'], $dateFormatPHP['time']);
        $sheet_title = sprintf("%s %s - %s", $date, $start, $end);
        
        list($date_formatted, $start_formatted, $end_formatted) = $sheet->getDateStartEnd($language, 'yyyy-MM-dd','yyyy-MM-dd_HH-mm' , "HH-mm");
        $sheet_title_formatted = sprintf("%s_%s-%s", $date_formatted, $start_formatted, $end_formatted);

        $sheet_categories = $this->sheet_mapper->getCategoriesWithNamesFromSheet($sheet_id);

        $fields = $this->noticefield_service->getNoticeFields($project->id);

        $response_data = [
            'sheet' => $sheet,
            'sheet_categories' => $sheet_categories,
            'sheet_title' => $sheet_title,
            'sheet_title_formatted' => $sheet_title_formatted,
            'project' => $project,
            'hasTimesheetNotice' => true,
            'fields' => $fields
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function getData($hash, $sheet_id) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        if (!$this->sheet_service->isChildOf($project->id, $sheet_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->mapper->getNotice($sheet_id);

        $response_data = [
            'entry' => $entry
        ];

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

}
