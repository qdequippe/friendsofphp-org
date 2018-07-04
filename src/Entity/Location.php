<?php declare(strict_types=1);

namespace Fop\Entity;

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
     * @var float
     */
    private $longitude;

    /**
     * @var float
     */
    private $latitude;

    public function __construct(string $city, string $country, float $longitude, float $latitude)
    {
        $this->city = $city;
        $this->country = $country;
        $this->longitude = $longitude;
        $this->latitude = $latitude;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }
}
