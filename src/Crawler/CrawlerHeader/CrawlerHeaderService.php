<?php

namespace App\Crawler\CrawlerHeader;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;

class CrawlerHeaderService extends \App\Base\Service {

    protected $dataobject = \App\Crawler\CrawlerHeader\CrawlerHeader::class;
    protected $dataobject_parent = \App\Crawler\Crawler::class;
    protected $element_view_route = 'crawlers_headers_edit';
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

    public function getFromCrawler($crawler_id, $hide_diff = false) {
        return $this->mapper->getFromCrawler($crawler_id, 'position', $hide_diff);
    }

    public function getInitialSortColumn($crawler_id) {
        return $this->mapper->getInitialSortColumn($crawler_id);
    }

    public function cloneHeaders(\App\Crawler\Crawler $target, \App\Crawler\Crawler $destination) {
        $clone_elements = $this->mapper->getFromCrawler($target->id);
        foreach ($clone_elements as &$clone) {
            $fromID = $clone->id;
            $clone->crawler = $destination->id;
            $clone->id = null;
            $id = $this->mapper->insert($clone);

            $this->logger->addNotice("Duplicate crawler headline", array("from" => $target->id, "to" => $destination->id, "fromID" => $fromID, "toID" => $id));
        }
    }

    public function unsetSortingForOtherHeaders($id) {
        $header = $this->mapper->get($id);

        // only one header can be initial sorted 
        // so remove the sort value on all others
        if (!is_null($header->sort)) {
            $this->mapper->unset_sort($id, $header->crawler);
        }
    }

    public function getSortOptions() {
        return [
            null => $this->translation->getTranslatedString('NO_INITIAL_SORTING'),
            "asc" => $this->translation->getTranslatedString('ASC'),
            "desc" => $this->translation->getTranslatedString('DESC')
        ];
    }

    // @see https://dev.mysql.com/doc/refman/8.0/en/cast-functions.html#function_cast
    public function getCastOptions() {
        return [
            null => $this->translation->getTranslatedString('CAST_NONE'),
            "BINARY" => $this->translation->getTranslatedString('CAST_BINARY'),
            "CHAR" => $this->translation->getTranslatedString('CAST_CHAR'),
            "DATE" => $this->translation->getTranslatedString('CAST_DATE'),
            "DATETIME" => $this->translation->getTranslatedString('CAST_DATETIME'),
            "DECIMAL" => $this->translation->getTranslatedString('CAST_DECIMAL'),
            "SIGNED" => $this->translation->getTranslatedString('CAST_SIGNED'),
            "TIME" => $this->translation->getTranslatedString('CAST_TIME'),
            "UNSIGNED" => $this->translation->getTranslatedString('CAST_UNSIGNED'),
        ];
    }

    protected function getElementViewRoute($entry) {
        $crawler = $this->getParentObjectService()->getEntry($entry->getParentID());
        $this->element_view_route_params["crawler"] = $crawler->getHash();
        return parent::getElementViewRoute($entry);
    }

}
