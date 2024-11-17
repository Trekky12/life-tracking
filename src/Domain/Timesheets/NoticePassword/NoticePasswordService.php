<?php

namespace App\Domain\Timesheets\NoticePassword;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Timesheets\Project\ProjectMapper;
use App\Application\Payload\Payload;

class NoticePasswordService extends Service {

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ProjectMapper $mapper
    ) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
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
        $testMessageEncryptedWithKEK = array_key_exists('testMessageEncryptedWithKEK', $data) ? filter_var($data['testMessageEncryptedWithKEK'], FILTER_SANITIZE_SPECIAL_CHARS) : null;
        $masterKeyEncryptedWithKEK = array_key_exists('masterKeyEncryptedWithKEK', $data) ? filter_var($data['masterKeyEncryptedWithKEK'], FILTER_SANITIZE_SPECIAL_CHARS) : null;
        $masterKeyEncryptedWithRecoveryKey = array_key_exists('masterKeyEncryptedWithRecoveryKey', $data) ? filter_var($data['masterKeyEncryptedWithRecoveryKey'], FILTER_SANITIZE_SPECIAL_CHARS) : null;
        $recoveryKeyEncryptedWithMasterKey = array_key_exists('recoveryKeyEncryptedWithMasterKey', $data) ? filter_var($data['recoveryKeyEncryptedWithMasterKey'], FILTER_SANITIZE_SPECIAL_CHARS) : null;
        $testMessageEncryptedWithRecoveryKey = array_key_exists('testMessageEncryptedWithRecoveryKey', $data) ? filter_var($data['testMessageEncryptedWithRecoveryKey'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        if (!is_null($salt) && !is_null($iterations) && !is_null($masterKeyEncryptedWithKEK) && !is_null($testMessageEncryptedWithKEK)) {
            $this->getMapper()->setEncryptionParameters($project->id, $salt, $iterations, $masterKeyEncryptedWithKEK, $testMessageEncryptedWithKEK, $masterKeyEncryptedWithRecoveryKey, $recoveryKeyEncryptedWithMasterKey, $testMessageEncryptedWithRecoveryKey);

            return new Payload(Payload::$RESULT_JSON, [
                "status" => "success"
            ]);
        }

        return new Payload(Payload::$RESULT_JSON, [
            "status" => "error"
        ]);
    }
}
