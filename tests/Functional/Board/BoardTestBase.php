<?php

namespace Tests\Functional\Board;

use Tests\Functional\Base\BaseTestCase;

class BoardTestBase extends BaseTestCase {

    protected $uri_overview = "/boards/";
    protected $uri_edit = "/boards/edit/";
    protected $uri_save = "/boards/save/";
    protected $uri_delete = "/boards/delete/";
    protected $uri_view = "/boards/view/HASH";
    protected $uri_child_edit = "/trips/HASH/event/edit/";
    protected $uri_child_save = "/trips/HASH/event/save/";
    protected $uri_child_delete = "/trips/HASH/event/delete/";

    protected function getParent($body, $name) {
        $matches = [];
        $re = '/<tr>\s*<td><a href="\/boards\/view\/(?<hash>.*)">' . preg_quote($name) . '<\/a><\/td>\s*(<td>\s*<\/td>\s*)*<td>\s*<a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>[0-9]*)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>[0-9]*)" class="btn-delete" data-type="board">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);
        
        return $matches;
    }

    protected function getParents($body) {
        $matches = [];
        $re = '/<tr>\s*<td><a href="\/boards\/view\/(?<hash>.*)">(?<name>.*)<\/a><\/td>\s*(<td>\s*<\/td>\s*)*<\/tr>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        return $matches;
    }

    protected function getLabel($body, $data) {
        $matches = [];
        $re = '/<span class="card-label" style="background-color:' . $data["background_color"] . '; color:' . $data["text_color"] . '"><a href="#" class="edit-label" data-label="(?<id>[0-9]+)">' . preg_quote($data["name"]) . '<\/a><\/span>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getStack($body, $name = "") {
        $matches = [];
        $re = '/<div class="stack-header" data-stack="(?<id>[0-9]+)">\s*<span class="title">' . preg_quote($name) . '<\/span>\s*<span class="edit-bar">\s*.*?\s*.*?\s*<\/span>\s*<\/div>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getCard($body, $title) {
        $matches = [];
        $re = '/<div class="board-card" id="card_[0-9]+" data-card="(?<id>[0-9]+)">.*<div class="card-title">' . preg_quote($title) . '<\/div>/s';
        preg_match($re, $body, $matches);

        return $matches;
    }

}
