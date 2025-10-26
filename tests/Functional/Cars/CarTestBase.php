<?php

namespace Tests\Functional\Cars;

use Tests\Functional\Base\BaseTestCase;

class CarTestBase extends BaseTestCase {

    protected $uri_overview = "/cars/";
    protected $uri_edit = "/cars/edit/";
    protected $uri_save = "/cars/save/";
    protected $uri_delete = "/cars/delete/";

    protected $uri_view = "/cars/HASH/refuel/";

    protected function getParent($body, $name) {
        $matches = [];
        $re = '/<tr>\s*<td>\s*<a href="\/cars\/(?<hash>.*)\/refuel\/">' . preg_quote($name ?? '') . '<\/a>\s*<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>[0-9]*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>[0-9]*)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }
}
