<?php declare(strict_types=1);

namespace Fop\Meetup\ValueObject;

use Location\Coordinate;

final class Location
{
    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $country;

    /**
     * @var Coordinate
     */
    private $coordinate;

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
}
