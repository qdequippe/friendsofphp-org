<?php declare(strict_types=1);

namespace Fop\Entity;

use DateTimeInterface;

final class Meetup
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $userGroupName;

    /**
     * @var string
     */
    private $url;

    /**
     * @var Location
     */
    private $location;

    /**
     * @var DateTimeInterface
     */
    private $startDateTime;

    public function __construct(
        string $name,
        string $userGroupName,
        DateTimeInterface $startDateTime,
        Location $location,
        string $url
    ) {
        $this->name = $name;
        $this->userGroupName = $userGroupName;
        $this->location = $location;
        $this->url = $url;
        $this->startDateTime = $startDateTime;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getCity(): string
    {
        return $this->location->getCity();
    }

    public function getCountry(): string
    {
        return $this->location->getCountry();
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getStartDateTime(): DateTimeInterface
    {
        return $this->startDateTime;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUserGroup(): string
    {
        return $this->userGroupName;
    }

    public function getLatitude(): float
    {
        return $this->location->getCoordinate()->getLat();
    }

    public function getLongitude(): float
    {
        return $this->location->getCoordinate()->getLng();
    }
}
