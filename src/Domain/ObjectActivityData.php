<?php

namespace App\Domain;

interface ObjectActivityData {

    public function getParentMapper();
    
    public function getParentID($entry): ?int;

    public function getModule(): string;

    public function getObjectViewRoute(): string;

    public function getObjectViewRouteParams($entry): array;

    public function getObjectLink($entry);
}
