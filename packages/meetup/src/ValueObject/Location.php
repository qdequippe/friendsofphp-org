<?php declare(strict_types=1);

namespace Fop\Meetup\ValueObject;

use Location\Coordinate;

final class Location
{
    private string $city;

    private string $country;

    private Coordinate $coordinate;

    public function __construct(string $city, string $country, Coordinate $coordinate)
    {
        $this->city = $city;
        $this->country = $country;
        $this->coordinate = $coordinate;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getCoordinate(): Coordinate
    {
        return $this->coordinate;
    }

    public function getCoordinateLatitude(): float
    {
        return $this->coordinate->getLat();
    }

    public function getCoordinateLongitude(): float
    {
        return $this->coordinate->getLng();
    }
}
