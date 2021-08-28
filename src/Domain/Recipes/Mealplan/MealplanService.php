<?php

namespace App\Domain\Recipes\Mealplan;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Base\Settings;
use App\Domain\Main\Translator;
use App\Application\Payload\Payload;

class MealplanService extends Service {

    private $settings;
    private $translation;

    public function __construct(LoggerInterface $logger, CurrentUser $user, MealplanMapper $mapper, Settings $settings, Translator $translation) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->settings = $settings;
        $this->translation = $translation;
    }

    public function index() {
        $mealplans = $this->getMealplansOfUser();

        return new Payload(Payload::$RESULT_HTML, ['mealplans' => $mealplans]);
    }

    public function edit($entry_id) {
        if ($this->isOwner($entry_id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);

        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry]);
    }

    public function view($hash, $from, $to) {

        $mealplan = $this->getFromHash($hash);

        if (!$this->isMember($mealplan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        // Week Filter
        $d1 = new \DateTime('monday this week');
        $minWeek = $d1->format('Y-m-d');
        $d2 = new \DateTime('sunday this week');
        $maxWeek = $d2->format('Y-m-d');

        $from = !is_null($from) ? $from : $minWeek;
        $to = !is_null($to) ? $to : $maxWeek;

        $dateInterval = [];
        if (!is_null($from) && !is_null($to)) {
            // add last day
            $dateMax = new \DateTime($to);
            $dateMax->add(new \DateInterval('P1D'));

            $dateInterval = new \DatePeriod(
                    new \DateTime($from),
                    new \DateInterval('P1D'),
                    $dateMax
            );
        }

        $recipes = $this->mapper->getMealplanRecipes($mealplan->id, $from, $to);

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

        $fmt = new \IntlDateFormatter($language, NULL, NULL);
        $fmt->setPattern($dateFormatPHP["mealplan_list"]);

        $dateRange = [];
        foreach ($dateInterval as $d) {
            $date = $d->format('Y-m-d');
            $recipes_of_day = array_key_exists($date, $recipes) ? $recipes[$date] : null;
            $dateRange[$date] = ["date" => $date, "full_date" => $fmt->format($d), "recipes" => $recipes_of_day];
        }

        return new Payload(Payload::$RESULT_HTML, [
            'mealplan' => $mealplan,
            'from' => $from,
            'to' => $to,
            'dates' => $dateRange,
            'isMealplanEdit' => true
        ]);
    }

    public function getMealplansOfUser() {
        return $this->mapper->getUserItems('t.createdOn DESC, name');
    }

    public function moveRecipeOnMealplan($hash, $data) {

        $mealplan = $this->getFromHash($hash);

        if (!$this->isMember($mealplan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $recipe = array_key_exists("recipe", $data) && !empty($data["recipe"]) ? intval(filter_var($data["recipe"], FILTER_SANITIZE_NUMBER_INT)) : null;
        $date = array_key_exists("date", $data) && !empty($data["date"]) ? filter_var($data["date"], FILTER_SANITIZE_STRING) : date('Y-m-d');
        $position = array_key_exists("position", $data) && !empty($data["position"]) ? intval(filter_var($data["position"], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $mealplan_recipe_id = array_key_exists("id", $data) && !empty($data["id"]) ? intval(filter_var($data["id"], FILTER_SANITIZE_NUMBER_INT)) : null;

        $notice = array_key_exists("notice", $data) && !empty($data["notice"]) ? filter_var($data["notice"], FILTER_SANITIZE_STRING) : null;

        if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)) {
            $date = date('Y-m-d');
        }

        $entry_id = null;

        if (is_null($mealplan_recipe_id)) {
            if (!is_null($recipe)) {
                $entry_id = $this->mapper->addRecipe($mealplan->id, $recipe, $date, $position);
            } elseif (!is_null($notice)) {
                $entry_id = $this->mapper->addRecipeNotice($mealplan->id, $notice, $date, $position);
            }
        } else {
            $entry_id = $this->mapper->moveRecipe($date, $position, $mealplan_recipe_id);
        }

        if (!is_null($entry_id)) {
            $this->logger->notice("Add Recipe to mealplan", array("recipe" => $recipe, "mealplan" => $mealplan->id));

            return new Payload(Payload::$STATUS_NEW, ["id" => $entry_id], $data);
        }

        return new Payload(Payload::$STATUS_ERROR);
    }

    public function removeRecipeFromMealplan($hash, $data) {

        $mealplan = $this->getFromHash($hash);

        if (!$this->isMember($mealplan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $mealplan_recipe_id = array_key_exists("mealplan_recipe_id", $data) && !empty($data["mealplan_recipe_id"]) ? intval(filter_var($data["mealplan_recipe_id"], FILTER_SANITIZE_NUMBER_INT)) : null;

        if (!is_null($mealplan_recipe_id)) {

            $result = $this->mapper->removeRecipe($mealplan_recipe_id);

            $this->logger->notice("Remove Recipe from mealplan", array("mealplan_recipe_id" => $mealplan_recipe_id, "mealplan" => $mealplan->id));

            return new Payload(Payload::$STATUS_DELETE_SUCCESS);
        }

        return new Payload(Payload::$STATUS_DELETE_ERROR);
    }

    public function addRecipe($data) {

        $stack = array_key_exists("stack", $data) && !empty($data["stack"]) ? filter_var($data['stack'], FILTER_SANITIZE_NUMBER_INT) : null;
        $card = array_key_exists("card", $data) && !empty($data["card"]) ? filter_var($data['card'], FILTER_SANITIZE_NUMBER_INT) : null;

        $user = $this->current_user->getUser()->id;
        $user_cards = $this->mapper->getUserCards($user);
        $user_stacks = $this->stack_service->getUserStacks($user);

        $response_data = ['status' => 'error'];
        if (!is_null($stack) && !is_null($card) && in_array($stack, $user_stacks) && in_array($card, $user_cards)) {
            $this->mapper->moveCard($card, $stack, $user);

            $response_data = ['status' => 'success'];
        }

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

}
