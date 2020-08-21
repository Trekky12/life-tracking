<?php

namespace App\Domain\Workouts\Exercise;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Base\Settings;
use App\Domain\Workouts\Muscle\MuscleMapper;
use App\Domain\Workouts\Bodypart\BodypartMapper;

class ExerciseService extends Service {

    private $settings;
    private $muscle_mapper;
    private $bodypart_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ExerciseMapper $mapper, Settings $settings, MuscleMapper $muscle_mapper, BodypartMapper $bodypart_mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;

        $this->settings = $settings;
        $this->muscle_mapper = $muscle_mapper;
        $this->bodypart_mapper = $bodypart_mapper;
    }

    public function index() {
        $exercises = $this->mapper->getAll();
        return new Payload(Payload::$RESULT_HTML, ['exercises' => $exercises]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);

        $categories = Exercise::getCategories();

        $muscles = $this->muscle_mapper->getAll('name');
        $bodyparts = $this->bodypart_mapper->getAll('name');

        $muscle_groups_primary = $this->mapper->getMuscleGroups($entry_id, true);
        $muscle_groups_secondary = $this->mapper->getMuscleGroups($entry_id, false);

        return new Payload(Payload::$RESULT_HTML, [
            'entry' => $entry,
            'categories' => $categories,
            'muscles' => $muscles,
            'bodyparts' => $bodyparts,
            'muscle_groups_primary' => $muscle_groups_primary,
            'muscle_groups_secondary' => $muscle_groups_secondary
        ]);
    }

    public function deleteImage($exercise_id, $thumbnail = false) {
        try {
            $exercise = $this->mapper->get($exercise_id);

            $folder = $this->getFullImagePath();

            if ($thumbnail) {
                $thumbnail = $exercise->get_thumbnail('small');
                unlink($folder . "/" . $thumbnail);
            } else {
                $image = $exercise->get_image();
                unlink($folder . "/" . $image);
            }

            $this->logger->notice("Delete Exercise Image", array("id" => $exercise_id));
        } catch (\Exception $e) {
            $this->logger->notice("Delete Exercise Image Error", array("id" => $exercise_id, 'error' => $e->getMessage()));
        }
    }

    public function getFullImagePath() {
        return dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . $this->getImagePath();
    }

    private function getImagePath() {
        return $this->settings->getAppSettings()['upload_folder'] . '/exercises/';
    }

}
