<?php

namespace App\Domain\Activity;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Base\Settings;
use App\Domain\User\UserMapper;
use App\Domain\Main\Translator;
use App\Domain\Main\Utility\Utility;

class ActivityService extends Service {

    private $settings;
    private $user_mapper;
    private $translation;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityMapper $mapper, Settings $settings, UserMapper $user_mapper, Translator $translation) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->settings = $settings;
        $this->user_mapper = $user_mapper;
        $this->translation = $translation;
    }

    public function show() {
        return new Payload(Payload::$RESULT_HTML);
    }

    public function getActivities($data) {

        $response_data = ["data" => [], "status" => "success"];
        $count = array_key_exists('count', $data) ? filter_var($data['count'], FILTER_SANITIZE_NUMBER_INT) : 20;
        $offset = array_key_exists('start', $data) ? filter_var($data['start'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $limit = sprintf("%s,%s", $offset, $count);

        $user_id = $this->current_user->getUser()->id;

        $activities = $this->mapper->getUserItems("createdOn DESC, id DESC", $limit, $user_id);

        $response_data["data"] = $this->renderTableRows($activities);
        $response_data["count"] = $this->mapper->getCountElementsOfUser($user_id);

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    private function renderTableRows($list) {

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

        $fmtDate = new \IntlDateFormatter($language);
        $fmtDate->setPattern($dateFormatPHP["date"]);

        $fmtTime = new \IntlDateFormatter($language);
        $fmtTime->setPattern($dateFormatPHP["time"]);

        $modules = $this->settings->getAppSettings()['modules'];

        $users = $this->user_mapper->getAll();
        $me = $this->current_user->getUser()->id;

        $rendered_data = [];
        foreach ($list as $el) {

            $action = "";
            switch ($el->type) {
                case 'create':
                    $action = "ACTIVITY_CREATE";
                    break;
                case 'update':
                    $action = "ACTIVITY_UPDATE";
                    break;
                case 'delete':
                    $action = "ACTIVITY_DELETE";
                    break;
                case 'add':
                    $action = "ACTIVITY_ADD";
                    break;
                case 'done':
                    $action = "ACTIVITY_DONE";
                    break;
                case 'undone':
                    $action = "ACTIVITY_UNDONE";
                    break;
                case 'archived':
                    $action = "ACTIVITY_ARCHIVED";
                    break;
                case 'unarchived':
                    $action = "ACTIVITY_UNARCHIVED";
                    break;
            }

            // special case since sheet notices are autosaved as new revisions
            if ($el->object == "App\Domain\Timesheets\SheetNotice\SheetNotice") {
                $action = "ACTIVITY_SAVE";
            }

            $object_description = !is_null($el->object_description) ? sprintf("\"%s\"", $el->object_description) : $this->translation->getTranslatedString($el->object::$NAME);

            if (is_null($el->user)) {
                $action = $action . "_SYSTEM";
                $description = sprintf($this->translation->getTranslatedString($action), $object_description);
            } elseif ($el->user === $me) {
                $action = $action . "_ME";
                $description = sprintf($this->translation->getTranslatedString($action), $object_description);
            } else {
                $user = $users[$el->user]->name;
                $description = sprintf($this->translation->getTranslatedString($action), $user, $object_description);
            }


            if ($el->parent_object && $el->parent_object_description) {
                $parent_object = $this->translation->getTranslatedString($el->parent_object::$NAME);
                $description .= sprintf(" (%s: %s)", $parent_object, trim($el->parent_object_description));
            }

            $row = [];
            $row["date"] = $fmtDate->format(new \Datetime($el->createdOn));
            $row["time"] = $fmtTime->format(new \Datetime($el->createdOn));
            $row["icon"] = array_key_exists($el->module, $modules) ? Utility::getFontAwesomeIcon($modules[$el->module]['icon']) : Utility::getFontAwesomeIcon("fas fa-toolbox");
            $row["description"] = $description;
            $row["link"] = $el->type !== 'delete' ? $el->link : null;

            $rendered_data[] = $row;
        }

        return $rendered_data;
    }
}
