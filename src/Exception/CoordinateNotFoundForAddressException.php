<?php

declare(strict_types=1);

namespace Fop\Exception;

final class CoordinateNotFoundForAddressException extends \RuntimeException
{
    public static function create(string $address): self
    {
        return new self(sprintf('No coordinates found for this address: %s', $address));
    }
}
