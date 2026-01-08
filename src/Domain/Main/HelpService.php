<?php

namespace App\Domain\Main;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Base\Settings;

class HelpService extends Service {

    private $settings;

    public function __construct(LoggerInterface $logger, CurrentUser $user, Settings $settings) {
        parent::__construct($logger, $user);
        $this->settings = $settings;
    }

    public function getHelpPage() {

        $template = $this->settings->getAppSettings()['i18n']['template'];

        $payload = new Payload(Payload::$RESULT_HTML, []);

        $helpFile = __DIR__ . '/../../../templates/help/' . $template . '.twig';
        if (file_exists($helpFile)) {
            $payload = $payload->withAdditionalData(["template" => $template]);
        }
        return $payload;
    }

}