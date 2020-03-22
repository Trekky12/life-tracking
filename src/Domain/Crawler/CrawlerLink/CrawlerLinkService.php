<?php

namespace App\Domain\Crawler\CrawlerLink;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;

class CrawlerLinkService extends \App\Domain\Service {

    protected $dataobject = \App\Domain\Crawler\CrawlerLink\CrawlerLink::class;
    protected $dataobject_parent = \App\Domain\Crawler\Crawler::class;
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
