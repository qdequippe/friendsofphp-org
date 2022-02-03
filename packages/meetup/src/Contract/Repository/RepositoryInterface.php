<?php

declare(strict_types=1);

namespace Fop\Meetup\Contract\Repository;

interface RepositoryInterface
{
    public function getTable(): string;
}
