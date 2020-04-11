<?php

namespace App\Domain\Finances\Category;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class CategoryService extends Service {

    public function __construct(LoggerInterface $logger, CurrentUser $user, CategoryMapper $mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
    }

    public function getAllCategoriesOrderedByName() {
        return $this->mapper->getAll('name');
    }

    public function getCategoryName($id) {
        return $this->mapper->get($id)->name;
    }

    public function getDefaultCategoryOfUser($user_id) {
        return $this->mapper->getDefaultofUser($user_id);
    }
    
    public function index(){
        $categories = $this->getAllCategoriesOrderedByName();
        return new Payload(Payload::$RESULT_HTML, ["categories" => $categories]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry]);
    }

}
