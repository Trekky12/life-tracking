<?php

namespace Tests\Functional\Splitbill;

use Tests\Functional\Base\BaseTestCase;

class SplitbillTestBase extends BaseTestCase {

    protected $uri_overview = "/splitbills/groups/";
    protected $uri_edit = "/splitbills/groups/edit/";
    protected $uri_save = "/splitbills/groups/save/";
    protected $uri_delete = "/splitbills/groups/delete/";
    protected $uri_view = "/splitbills/HASH/view/";
    protected $uri_child_edit = "/splitbills/HASH/bills/edit/";
    protected $uri_child_save = "/splitbills/HASH/bills/save/";
    protected $uri_child_delete = "/splitbills/HASH/bills/delete/";
    
    protected $uri_recurring_view = "/splitbills/HASH/recurring/";
    protected $uri_recurring_edit = "/splitbills/HASH/recurring/edit/";
    protected $uri_recurring_save = "/splitbills/HASH/recurring/save/";
    protected $uri_recurring_delete = "/splitbills/HASH/recurring/delete/";
    protected $uri_recurring_trigger = "/splitbills/HASH/recurring/trigger/";

    protected function getParent($body, $name) {
        $matches = [];
        $re = '/<tr>\s*<td><a href="\/splitbills\/(?<hash>.*)\/view\/">' . preg_quote($name) . '<\/a><\/td>\s*(<td(.*)?>\s*<\/td>\s*)*<td>\s*<a href="' . str_replace('/', "\/", $this->uri_edit) . '(?<id_edit>.*)"><span class="fas fa-edit fa-lg"><\/span><\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->uri_delete) . '(?<id_delete>.*)" class="btn-delete"><span class="fas fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

    protected function getParents($body) {
        $matches = [];
        $re = '/<tr>\s*<td><a href="\/splitbills\/(?<hash>.*)\/view\/">(?<name>.*)<\/a><\/td>\s*(<td(.*)?>.*?|\s*<\/td>\s*)*<\/tr>/';
        preg_match_all($re, $body, $matches, PREG_SET_ORDER);

        return $matches;
    }

    protected function getChild($body, $data, $hash, $user = 1) {
        
        $spend = number_format($data["balance"][$user]["spend"], 2);
        $paid = number_format($data["balance"][$user]["paid"], 2);
        $diff = number_format($paid - $spend, 2);
        
        $matches = [];
        $re = '/<tr>\s*<td>' . preg_quote($data["date"]) . '<\/td>\s*<td>' . preg_quote($data["time"]) . '<\/td>\s*<td>' . $data["name"] . '<\/td>\s*<td><\/td>\s*<td>' . preg_quote($spend) . '<\/td>\s*<td>' . preg_quote($paid) . '<\/td>\s*<td>' . preg_quote($diff) . '<\/td>\s*<td><a href="' . str_replace('/', "\/", $this->getURIChildEdit($hash)) . '(?<id_edit>.*)"><span class="fas fa-edit fa-lg"><\/span><\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->getURIChildDelete($hash)) . '(?<id_delete>.*)" class="btn-delete"><span class="fas fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }
    
    protected function getURIRecurringView($hash) {
        return str_replace("HASH", $hash, $this->uri_recurring_view);
    }
    protected function getURIRecurringEdit($hash) {
        return str_replace("HASH", $hash, $this->uri_recurring_edit);
    }
    protected function getURIRecurringSave($hash) {
        return str_replace("HASH", $hash, $this->uri_recurring_save);
    }
    protected function getURIRecurringDelete($hash) {
        return str_replace("HASH", $hash, $this->uri_recurring_delete);
    }
    protected function getURIRecurringTrigger($hash) {
        return str_replace("HASH", $hash, $this->uri_recurring_trigger);
    }
    
    protected function getRecurring($body, $data, $hash, $user = 1) {
        
        $spend = number_format($data["balance"][$user]["spend"], 2);
        $paid = number_format($data["balance"][$user]["paid"], 2);
        $diff = number_format($paid - $spend, 2);
        
        $matches = [];
        $re = '/<tr>\s*<td>' . $data["name"] . '<\/td>\s*<td><\/td>\s*<td>' . preg_quote($spend) . '<\/td>\s*<td>' . preg_quote($paid) . '<\/td>\s*<td>' . preg_quote($diff) . '<\/td>\s*<td>' . preg_quote($data["start"]) . '<\/td>\s*<td>' . preg_quote($data["end"]) . '<\/td>\s*<td>' . $data['multiplier'] . ' x Tag' . '<\/td>\s*<td><\/td>\s*<td><\/td>\s*<td>x<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->getURIRecurringTrigger($hash)) . '(?<id_trigger>.*)"><span class="fas fa-play fa-lg"><\/span><\/a>\s*<\/td>\s*<td>\s*<a href="' . str_replace('/', "\/", $this->getURIRecurringEdit($hash)) . '(?<id_edit>.*)"><span class="fas fa-edit fa-lg"><\/span><\/a>\s*<\/td>\s*<td>\s*<a href="#" data-url="' . str_replace('/', "\/", $this->getURIRecurringDelete($hash)) . '(?<id_delete>.*)" class="btn-delete"><span class="fas fa-trash fa-lg"><\/span><\/a>\s*<\/td>\s*<\/tr>/';
        preg_match($re, $body, $matches);

        return $matches;
    }

}
