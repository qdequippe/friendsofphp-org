<?php

declare(strict_types=1);

namespace Fop\Contract;

use Location\Coordinate;

interface GeocoderInterface
{
    public function retrieveCoordinate(string $address): Coordinate;
}
