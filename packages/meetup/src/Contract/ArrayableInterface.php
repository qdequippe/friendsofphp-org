<?php

declare(strict_types=1);

namespace Fop\Meetup\Contract;

interface ArrayableInterface
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
