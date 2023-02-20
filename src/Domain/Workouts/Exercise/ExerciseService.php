<?php

namespace App\Domain\Workouts\Exercise;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Base\Settings;
use App\Domain\Workouts\Muscle\MuscleMapper;
use App\Domain\Workouts\Bodypart\BodypartMapper;
use App\Domain\Settings\SettingsMapper;

class ExerciseService extends Service {

    private $settings;
    private $muscle_mapper;
    private $bodypart_mapper;
    protected $settings_mapper;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            ExerciseMapper $mapper,
            Settings $settings,
            MuscleMapper $muscle_mapper,
            BodypartMapper $bodypart_mapper,
            SettingsMapper $settings_mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;

        $this->settings = $settings;
        $this->muscle_mapper = $muscle_mapper;
        $this->bodypart_mapper = $bodypart_mapper;

        $this->settings_mapper = $settings_mapper;
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

    public function view() {
        $bodyparts = $this->bodypart_mapper->getAll();
        return new Payload(Payload::$RESULT_HTML, ['bodyparts' => $bodyparts]);
    }

    public function getExercises($data) {

        $response_data = ["data" => [], "status" => "success"];
        $count = array_key_exists('count', $data) ? filter_var($data['count'], FILTER_SANITIZE_NUMBER_INT) : 20;
        $offset = array_key_exists('start', $data) ? filter_var($data['start'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $limit = sprintf("%s,%s", $offset, $count);

        $bodypart = array_key_exists('bodypart', $data) ? intval(filter_var($data['bodypart'], FILTER_SANITIZE_NUMBER_INT)) : -1;
        $query = array_key_exists('query', $data) ? filter_var($data['query'], FILTER_SANITIZE_STRING) : '';

        $exercises = $this->mapper->getExercisesWithBodyPart("name ASC", $limit, $bodypart, $query);
        $bodyparts = $this->bodypart_mapper->getAll();
        $muscles = $this->muscle_mapper->getAll();

        $exercise_ids = array_map(function($exercise) {
            return $exercise->id;
        }, $exercises);

        $exercise_muscles = $this->mapper->getMusclesOfExercisesFull($exercise_ids);

        $exercises_print = [];
        $exercise_idx = 0;
        foreach ($exercises as $exercise) {

            // get muscles
            $primary = [];
            $secondary = [];
            if (array_key_exists($exercise->id, $exercise_muscles)) {
                foreach ($exercise_muscles[$exercise->id] as $em) {
                    if ($em["is_primary"] > 0) {
                        $primary[] = $muscles[$em["muscle"]];
                    } else {
                        $secondary[] = $muscles[$em["muscle"]];
                    }
                }
            }

            $exercises_print[] = [
                "exercise" => $exercise,
                "mainBodyPart" => $bodyparts[$exercise->mainBodyPart]->name,
                "mainMuscle" => $muscles[$exercise->mainMuscle]->name,
                "primary_muscles" => $primary,
                "secondary_muscles" => $secondary,
                "idx" => $exercise_idx
            ];
            $exercise_idx++;
        }

        $response_data["data"] = $exercises_print;
        $response_data["count"] = $this->mapper->getExercisesWithBodyPartCount($bodypart, $query);

        // Get Muscle Image
        $baseMuscleImage = $this->settings_mapper->getSetting('basemuscle_image');
        $baseMuscleImageThumbnail = '';
        if ($baseMuscleImage && $baseMuscleImage->getValue()) {
            $size = "small";
            $file_extension = pathinfo($baseMuscleImage->getValue(), PATHINFO_EXTENSION);
            $file_wo_extension = pathinfo($baseMuscleImage->getValue(), PATHINFO_FILENAME);
            $baseMuscleImageThumbnail = $file_wo_extension . '-' . $size . '.' . $file_extension;
        }

        $response_data["baseMuscleImageThumbnail"] = $baseMuscleImageThumbnail;

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function getSelectedMuscles($data) {
        $response_data = ["data" => [], "status" => "success"];

        if (array_key_exists("exercises", $data) && is_array($data["exercises"]) && !empty($data["exercises"])) {
            $exercise_ids = filter_var_array($data["exercises"], FILTER_SANITIZE_NUMBER_INT);

            $muscles = $this->muscle_mapper->getAll();
            $exercise_muscles = $this->mapper->getMusclesOfExercises($exercise_ids);

            $primary = [];
            $secondary = [];
            foreach ($exercise_muscles as $em) {
                if ($em["is_primary"] > 0) {
                    $primary[] = $muscles[$em["muscle"]];
                } else {
                    $secondary[] = $muscles[$em["muscle"]];
                }
            }

            $response_data["data"]["primary"] = $primary;
            $response_data["data"]["secondary"] = $secondary;
            $response_data["data"]["small"] = false;
        }

        // Get Muscle Image
        $baseMuscleImage = $this->settings_mapper->getSetting('basemuscle_image');
        if ($baseMuscleImage && $baseMuscleImage->getValue()) {
            $size = "small";
            $file_extension = pathinfo($baseMuscleImage->getValue(), PATHINFO_EXTENSION);
            $file_wo_extension = pathinfo($baseMuscleImage->getValue(), PATHINFO_FILENAME);
            $baseMuscleImageThumbnail = $file_wo_extension . '-' . $size . '.' . $file_extension;
        }
        $response_data["data"]["baseMuscleImageThumbnail"] = $baseMuscleImageThumbnail;

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function getExercise($data) {

        $response_data = ["data" => [], "status" => "success"];
        $sets = array_key_exists('sets', $data) ? filter_var($data['sets'], FILTER_SANITIZE_NUMBER_INT) : 3;
        $exercise_id = array_key_exists('exercise', $data) ? filter_var($data['exercise'], FILTER_SANITIZE_NUMBER_INT) : -1;
        $exercise_idx = array_key_exists('count', $data) ? filter_var($data['count'], FILTER_SANITIZE_NUMBER_INT) : -1;

        try {
            $exercise = $this->mapper->get($exercise_id);
            $response_data["data"] = [
                "exercise" => $exercise,
                "idx" => $exercise_idx,
                "sets" => range(0, $sets - 1),
                "type" => "exercise",
                "notice" => null,
                "is_child" => 0,
                "children" => []
            ];
            $response_data["hidden"] = false;
        } catch (\Exception $e) {
            $response_data["status"] = "error";
        }

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

}
