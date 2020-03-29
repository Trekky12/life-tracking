<?php

namespace App\Domain\Activity;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;

class Controller {

    protected $logger;
    protected $twig;
    protected $settings;
    protected $translation;
    protected $current_user;
    private $mapper;
    private $user_mapper;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Settings $settings,
            Translator $translation,
            CurrentUser $current_user,
            ActivityMapper $mapper,
            \App\Domain\User\Mapper $user_mapper) {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->settings = $settings;
        $this->translation = $translation;
        $this->current_user = $current_user;

        $this->user_mapper = $user_mapper;
        $this->mapper = $mapper;
    }

    /**
     * @deprecated since general service
     */
    public function addEntry($type, $module, $controller, $object = [], $parent = [], $users = []) {
        $data = [];
        $data["user"] = $this->current_user->getUser()->id;
        $data["type"] = $type;
        $data["module"] = $module;
        $data["controller"] = $controller;
        $data["object"] = array_key_exists("object", $object) ? $object["object"] : null;
        $data["object_id"] = array_key_exists("id", $object) ? $object["id"] : null;
        $data["object_description"] = array_key_exists("description", $object) ? $object["description"] : null;
        $data["parent_object"] = array_key_exists("object", $parent) ? $parent["object"] : null;
        $data["parent_object_id"] = array_key_exists("id", $parent) ? $parent["id"] : null;
        $data["parent_object_description"] = array_key_exists("description", $parent) ? $parent["description"] : null;
        $data["link"] = array_key_exists("link", $object) ? $object["link"] : null;

        $activity = new Activity($data);
        $id = $this->mapper->insert($activity);

        $this->mapper->addUsers($id, $users);
    }

    

    
}
