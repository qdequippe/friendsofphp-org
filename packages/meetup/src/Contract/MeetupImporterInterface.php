<?php declare(strict_types=1);

namespace Fop\Meetup\Contract;

use Fop\Core\ValueObject\Meetup;

interface MeetupImporterInterface
{
    public function getKey(): string;

    /**
     * @return Meetup[]
     */
    public function getMeetups(): array;
}
