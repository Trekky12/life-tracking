<?php

namespace Tests\Functional\Trip;

use Tests\Functional\Base\BaseTestCase;

class TripTestBase extends BaseTestCase {

    protected $uri_overview = "/trips/";
    protected $uri_edit = "/trips/edit/";
    protected $uri_save = "/trips/save/";
    protected $uri_delete = "/trips/delete/";
    protected $uri_view = "/trips/HASH/view/";
    protected $uri_child_edit = "/trips/HASH/tripevent/edit/";
    protected $uri_child_save = "/trips/HASH/tripevent/save/";
    protected $uri_child_delete = "/trips/HASH/tripevent/delete/";
    protected $uri_waypoint_add = "/trips/HASH/waypoint/add";
    protected $uri_waypoint_delete = "/trips/HASH/waypoint/delete";
    protected $uri_route_add = "/trips/HASH/route/add";
    protected $uri_route_delete = "/trips/HASH/route/delete/";
    protected $uri_route_list = "/trips/HASH/route/list";
    protected $uri_route_waypoints = "/trips/HASH/route/getWaypoints";

    protected function getParent($body, $name) {
        $matches = [];
        $re = '/<tr>\s*<td><a href="\/trips\/(?<hash>.*)\/view\/">' . preg_quote($name) . '<\/a><\/td>\s*(<td>\s*<\/td>\s*)*<td>\s*<a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>.*)"><span class="fas fa-edit fa-lg"><\/span><\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>.*)" class="btn-delete"><span class="fas fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getParents($body) {
        $matches = [];
        $re = '/<tr>\s*<td><a href="\/trips\/(?<hash>.*)\/view\/">(?<name>.*)<\/a><\/td>\s*(<td>[\s0-9-]*<\/td>\s*)*<\/tr>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        return $matches;
    }

    protected function getChild($body, $name) {
        $matches = [];
        $re = '/<div class="trip_event\s*(has_notice)?" data-event="(?<id>[0-9]*)">\s*<div class="icon">\s*<i class="(.*)?"><\/i>\s*<\/div>\s*<div class="text">\s*<h4>' . $name . '<\/h4>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getURIWaypointAdd($hash) {
        return str_replace("HASH", $hash, $this->uri_waypoint_add);
    }

    protected function getURIWaypointDelete($hash) {
        return str_replace("HASH", $hash, $this->uri_waypoint_delete);
    }

    protected function getURIRouteAdd($hash) {
        return str_replace("HASH", $hash, $this->uri_route_add);
    }

    protected function getURIRouteDelete($hash) {
        return str_replace("HASH", $hash, $this->uri_route_delete);
    }
    
    protected function getURIRouteList($hash) {
        return str_replace("HASH", $hash, $this->uri_route_list);
    }
    
    protected function getURIRouteWaypoints($hash) {
        return str_replace("HASH", $hash, $this->uri_route_waypoints);
    }

}
