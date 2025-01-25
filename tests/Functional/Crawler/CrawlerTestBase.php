<?php

namespace Tests\Functional\Crawler;

use Tests\Functional\Base\BaseTestCase;

class CrawlerTestBase extends BaseTestCase {

    protected $uri_overview = "/crawlers/";
    protected $uri_edit = "/crawlers/edit/";
    protected $uri_save = "/crawlers/save/";
    protected $uri_delete = "/crawlers/delete/";
    protected $uri_view = "/crawlers/HASH/view/";
    protected $uri_dataset_save = "/crawlers/HASH/save/";

    protected $TEST_CRAWLER_ID = 1;
    protected $TEST_CRAWLER_HASH = "ABCabc123";
    protected $TEST_CRAWLER_LINK_ID = 1;
    protected $TEST_CRAWLER_HEADER_ID = 1;

    protected function getParent($body, $name) {
        $matches = [];
        $re = '/<tr>\s*<td>\s*<a href="\/crawlers\/(?<hash>[0-9a-zA-Z]+)\/view\/">' . preg_quote($name ?? '') . '<\/a>\s*<\/td>\s*<td>\s*<a href="\/crawlers\/([0-9a-zA-Z]+)\/headers\/\">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="\/crawlers\/([0-9a-zA-Z]+)\/links\/\">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="\/crawlers\/edit\/(?<id_edit>[0-9]+)">.*?<\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="\/crawlers\/delete\/(?<id_delete>[0-9]+)" class="btn-delete">.*?<\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getParents($body) {
        $matches = [];
        $re = '/<tr>\s*<td><a href="\/crawlers\/(?<hash>.*)\/view\/">(?<name>.*)<\/a><\/td>\s*(<td>\s*<\/td>\s*)*<\/tr>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        return $matches;
    }

    protected function getChild($body, $name) {
        $matches = [];
        $re = '/<div class="trip_event\s*(has_notice)?" data-event="(?<id>[0-9]*)">(.*)?<h4>' . $name . '<\/h4>/s';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getURIDatasetSave($hash) {
        return str_replace("HASH", $hash, $this->uri_dataset_save);
    }
}
