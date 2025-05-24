<?php

namespace App\Domain\Timesheets\SheetFile;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Sheet\SheetMapper;
use App\Domain\Timesheets\Project\ProjectMapper;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Sheet\SheetService;
use App\Domain\Main\Translator;
use App\Domain\Timesheets\Customer\CustomerMapper;
use Slim\Routing\RouteParser;

class SheetFileWriter extends ObjectActivityWriter {

    private $service;
    private $sheet_service;
    private $sheet_mapper;
    private $project_service;
    private $project_mapper;
    private $translation;
    private $customer_mapper;
    private $router;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        SheetFileService $service,
        SheetService $sheet_service,
        SheetFileMapper $mapper,
        SheetMapper $sheet_mapper,
        ProjectMapper $project_mapper,
        ProjectService $project_service,
        Translator $translation,
        CustomerMapper $customer_mapper,
        RouteParser $router
    ) {
        parent::__construct($logger, $user, $activity);
        $this->service = $service;
        $this->sheet_service = $sheet_service;
        $this->mapper = $mapper;
        $this->sheet_mapper = $sheet_mapper;
        $this->project_mapper = $project_mapper;
        $this->project_service = $project_service;
        $this->translation = $translation;
        $this->customer_mapper = $customer_mapper;
        $this->router = $router;
    }

    public function save($id, $data, $additionalData = null): Payload {

        $project = $this->project_service->getFromHash($additionalData["project"]);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->sheet_service->isChildOf($project->id, $additionalData["sheet"])) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $data['sheet'] = $additionalData["sheet"];

        $files = array_key_exists("files", $additionalData) && is_array($additionalData["files"]) ? $additionalData["files"] : [];
        if (!empty($files) && array_key_exists('file', $files) && !empty($files['file'])) {
            $file = $files['file'];

            if ($file->getError() === UPLOAD_ERR_OK) {

                $uploadFileName = $file->getClientFilename();
                $file_name = $project->id . '_' . $data['sheet'] . '_' . hash('sha256', time() . rand(0, 1000000) . $data['sheet']) . '_' .  $uploadFileName . ".enc";
                $complete_file_name = $this->service->getFullPath($project->getHash(), $data['sheet']) . DIRECTORY_SEPARATOR . $file_name;

                $file->moveTo($complete_file_name);

                $data["name"] = $uploadFileName;
                $data["filename"] = $file_name;

                $this->logger->notice("Upload Sheet Notice File Set", array("id" => $data['sheet'], "image" => $file->getClientFilename()));

                $payload = parent::save(null, $data, $additionalData);
                $entry = $payload->getResult();

                $result = [
                    "data" => $this->service->load_file($project->getHash(), $additionalData['sheet'], $file_name),
                    "name" => $entry->name,
                    "type" => $entry->type,
                    "encryptedCEK" => $entry->encryptedCEK,
                    "delete" => $this->router->urlFor('timesheets_sheets_file_delete', ['sheet' => $additionalData['sheet'], 'project' => $project->getHash()]) . '?id=' . $entry->id
                ];
                return $payload->withAdditionalData($result);
            }
        }

        $this->logger->error("Upload Sheet Notice File Error", array("data" => $data, "files" => $files));
        return new Payload(Payload::$STATUS_ERROR, "No File");
    }

    public function getParentMapper() {
        return $this->sheet_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'timesheets_sheets_notice_view';
    }

    public function getObjectViewRouteParams($entry): array {
        $sheet = $this->getParentMapper()->get($entry->getParentID());
        $project = $this->project_mapper->get($sheet->getParentID());
        return [
            "project" => $project->getHash(),
            "sheet" => $sheet->id,
            "id" => $entry->id
        ];
    }

    public function getModule(): string {
        return "timesheets";
    }

    protected function getAdditionalInformation($entry): ?string {
        $sheet = $this->getParentMapper()->get($entry->getParentID());
        if ($sheet->customer) {
            $project = $this->project_mapper->get($sheet->getParentID());
            $customerDescription = $project->customers_name_singular ? $project->customers_name_singular : $this->translation->getTranslatedString("TIMESHEETS_CUSTOMER");

            $customer = $this->customer_mapper->get($sheet->customer);

            return sprintf("%s: %s", $customerDescription, $customer->name);
        }
        return parent::getAdditionalInformation($entry);
    }
}
