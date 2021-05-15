<?php

namespace App\Domain\Recipes\Cookbook;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Application\Payload\Payload;

class CookbookService extends Service {

    private $user_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, CookbookMapper $mapper, UserService $user_service) {
        parent::__construct($logger, $user);

        $this->mapper = $mapper;
        $this->user_service = $user_service;
    }

    public function index() {
        $cookbooks = $this->getCookbooksOfUser();

        return new Payload(Payload::$RESULT_HTML, ['cookbooks' => $cookbooks]);
    }

    public function edit($entry_id) {
        if ($this->isOwner($entry_id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        $users = $this->user_service->getAll();

        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'users' => $users]);
    }

    public function view($hash) {

        $cookbook = $this->getFromHash($hash);

        if (!$this->isMember($cookbook->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        return new Payload(Payload::$RESULT_HTML, ['cookbook' => $cookbook]);
    }

    public function getCookbooksOfUser() {
        return $this->mapper->getUserItems('t.createdOn DESC, name');
    }

    public function addRecipeToCookbook($data) {
        $recipe = array_key_exists("recipe", $data) && !empty($data["recipe"]) ? intval(filter_var($data["recipe"], FILTER_SANITIZE_NUMBER_INT)) : null;
        $cookbook_id = array_key_exists("cookbook", $data) && !empty($data["cookbook"]) ? intval(filter_var($data["cookbook"], FILTER_SANITIZE_NUMBER_INT)) : null;

        if (!is_null($recipe) && !is_null($cookbook_id)) {

            if ($this->isMember($cookbook_id) === false) {
                return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
            }

            $recipes = $this->getCookbookRecipes($cookbook_id);
            if (!in_array($recipe, $recipes)) {

                $entry_id = $this->mapper->addRecipe($cookbook_id, $recipe);

                $this->logger->notice("Add Recipe to cookbook", array("recipe" => $recipe, "cookbook" => $cookbook_id));

                return new Payload(Payload::$STATUS_NEW, null, $data);
            }
        }

        return new Payload(Payload::$STATUS_ERROR);
    }

    public function removeRecipeFromCookbook($cookbook_hash, $recipe_id) {

        if (!is_null($recipe_id) && !is_null($cookbook_hash)) {

            $cookbook = $this->getFromHash($cookbook_hash);

            if ($this->isMember($cookbook->id) === false) {
                return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
            }
            $recipes = $this->getCookbookRecipes($cookbook->id);
            if (in_array($recipe_id, $recipes)) {
                $result = $this->mapper->removeRecipe($cookbook->id, $recipe_id);

                $this->logger->notice("Remove Recipe from cookbook", array("recipe" => $recipe_id, "cookbook" => $cookbook->id));

                return new Payload(Payload::$STATUS_DELETE_SUCCESS, ["redirect" => '/']);
            }
        }

        return new Payload(Payload::$STATUS_DELETE_ERROR);
    }

    public function getCookbook($cookbook_id) {
        return $this->getEntry($entry_id);
    }
    
    public function getCookbookRecipes($cookbook_id){
        return $this->mapper->getCookbookRecipes($cookbook_id);
    }

}
