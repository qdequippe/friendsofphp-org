<?php

declare(strict_types=1);

namespace Fop\Meetup\Contract;

interface ArrayableInterface extends \JsonSerializable
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self;
}
