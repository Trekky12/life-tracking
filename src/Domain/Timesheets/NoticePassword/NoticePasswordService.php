<?php

namespace App\Domain\Timesheets\NoticePassword;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Domain\Timesheets\Sheet\SheetMapper;
use App\Domain\Timesheets\Project\ProjectMapper;
use App\Domain\Main\Translator;
use App\Application\Payload\Payload;
use App\Domain\Main\Utility\DateUtility;

class NoticePasswordService extends Service {

    private $user_service;
    private $sheet_mapper;
    private $translation;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ProjectMapper $mapper,
        UserService $user_service,
        SheetMapper $sheet_mapper,
        Translator $translation
    ) {
        parent::__construct($logger, $user);

        $this->mapper = $mapper;
        $this->user_service = $user_service;
        $this->sheet_mapper = $sheet_mapper;
        $this->translation = $translation;
    }

    public function index($hash) {
        $project = $this->getFromHash($hash);

        if (!$this->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $parameters = $this->getMapper()->getEncryptionParameters($project->id);

        return new Payload(Payload::$RESULT_HTML, [
            'project' => $project,
            'parameters' => $parameters,
            'hasTimesheetNotice' => true
        ]);
    }

    public function getEncryptionParameters($hash, $data) {
        $project = $this->getFromHash($hash);

        if (!$this->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $parameters = $this->getMapper()->getEncryptionParameters($project->id);

        return new Payload(Payload::$RESULT_JSON, [
            "status" => "success",
            "data" => $parameters
        ]);
    }

    public function setEncryptionParameters($hash, $data) {

        $project = $this->getFromHash($hash);

        if (!$this->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $salt = array_key_exists('salt', $data) ? filter_var($data['salt'], FILTER_SANITIZE_SPECIAL_CHARS) : null;
        $iterations = array_key_exists('iterations', $data) ? intval(filter_var($data['iterations'], FILTER_SANITIZE_NUMBER_INT)) : 600000;
        $test = array_key_exists('test', $data) ? filter_var($data['test'], FILTER_SANITIZE_SPECIAL_CHARS) : null;
        $KEK = array_key_exists('KEK', $data) ? filter_var($data['KEK'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        if (!is_null($salt) && !is_null($iterations) && !is_null($test) && !is_null($KEK)) {
            $this->getMapper()->setEncryptionParameters($project->id, $salt, $iterations, $test, $KEK);

            return new Payload(Payload::$RESULT_JSON, [
                "status" => "success"
            ]);
        }

        return new Payload(Payload::$RESULT_JSON, [
            "status" => "error"
        ]);
    }
}
