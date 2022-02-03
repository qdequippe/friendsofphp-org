<?php

declare(strict_types=1);

namespace Fop\Meetup\Contract;

interface ArrayableInterface
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self;
}
