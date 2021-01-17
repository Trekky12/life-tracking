<?php

namespace Tests\Functional\Cars;

use Tests\Functional\Base\BaseTestCase;

class CarTestBase extends BaseTestCase {

    protected $uri_overview = "/cars/control/";
    protected $uri_edit = "/cars/control/edit/";
    protected $uri_save = "/cars/control/save/";
    protected $uri_delete = "/cars/control/delete/";

    protected function getParent($body, $name) {
        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($name) . '<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>.*)"><span class="fas fa-edit fa-lg"><\/span><\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>.*)" class="btn-delete"><span class="fas fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

}
