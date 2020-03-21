<?php

namespace App\Crawler\CrawlerLink;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;

class CrawlerLinkService extends \App\Base\Service {

    protected $dataobject = \App\Crawler\CrawlerLink\CrawlerLink::class;
    protected $dataobject_parent = \App\Crawler\Crawler::class;
    protected $element_view_route = 'crawlers_links_edit';
    protected $module = "crawlers";

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
    }

    public function getFromCrawler($crawler_id) {
        return $this->mapper->getFromCrawler($crawler_id);
    }

    public function buildTree(array $elements, $parentId = null) {
        $branch = array();

        foreach ($elements as $element) {
            if ($element->parent == $parentId) {
                $children = $this->buildTree($elements, $element->id);
                if ($children) {
                    $element->children = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    protected function getElementViewRoute($entry) {
        $crawler = $this->getParentObjectService()->getEntry($entry->getParentID());
        $this->element_view_route_params["crawler"] = $crawler->getHash();
        return parent::getElementViewRoute($entry);
    }

}
