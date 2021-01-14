<?php

declare(strict_types=1);

namespace Fop\Meetup\Contract;

use Fop\Meetup\ValueObject\Meetup;

interface MeetupImporterInterface
{
    public function getKey(): string;

    /**
     * @return Meetup[]
     */
    public function getMeetups(): array;
}
