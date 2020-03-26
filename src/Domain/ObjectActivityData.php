<?php

namespace App\Domain;

interface ObjectActivityData {

    public function getParentMapper();

    public function getModule(): string;

    public function getObjectViewRoute(): string;

    public function getObjectViewRouteParams(int $id): array;

    public function getObjectLink(int $id);
}
