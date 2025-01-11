<?php

namespace App\Domain\Admin\Setup;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Base\Settings;
use App\Domain\Settings\SettingsMapper;

class SetupService extends Service {

    private $settings;
    private $settings_mapper;
    private $setup_mapper;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        Settings $settings,
        SettingsMapper $settings_mapper,
        SetupMapper $setup_mapper
    ) {
        parent::__construct($logger, $user);

        $this->settings = $settings;
        $this->settings_mapper = $settings_mapper;
        $this->setup_mapper = $setup_mapper;
    }

    public function getSetupPage() {
        return new Payload(Payload::$RESULT_HTML, []);
    }


    public function runUpgrade() {

        list($migrations, $output) = $this->runMigrations();

        return new Payload(Payload::$RESULT_HTML, ["migrations" => $migrations, "output" => $output]);
    }

    private function runMigrations() {

        $output = [];
        $migrations = [];

        $current_version = 0;
        if ($this->settings_mapper->exists()) {
            $current_version = $this->settings_mapper->getSetting("version")->getValue();
        }

        $path = __DIR__ . '/../../../../db/migrations/*.sql';
        $migrationFiles = glob($path);

        usort($migrationFiles, 'strnatcmp');

        foreach ($migrationFiles as $migrationFile) {
            $migrationName = basename($migrationFile, '.sql');
            $version = $this->extractVersionFromFilename($migrationName);

            if ($version === null) {
                continue;
            }

            if (version_compare($version, $current_version, '<=')) {
                continue;
            }

            $sql = file_get_contents($migrationFile);
            $migrations[] = ["version" => $version, "sql" => $sql];
        }

        foreach ($migrations as $migration) {

            $ret = $this->setup_mapper->runMigration($migration["sql"]);

            $output[] = "Applied migration: $migrationName " . ($ret ? "successfully" : "failed");

            $current_version = $migration["version"];
        }

        if ($this->settings_mapper->exists()) {
            $this->settings_mapper->addOrUpdateSetting('version',  $current_version, "Integer");
        }

        return [count($migrations) > 0 ? count($migrations) : -1, $output];
    }

    private function extractVersionFromFilename($filename) {
        $parts = explode('_', $filename, 2);
        return is_numeric($parts[0]) ? intval($parts[0]) : null;
    }
}
