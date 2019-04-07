<?php declare(strict_types=1);

namespace Fop\Entity;

use DateTimeInterface;
use Location\Coordinate;

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
     * @var string[]
     */
    private $tags = [];

    /**
     * @var Location
     */
    private $location;

    /**
     * @var DateTimeInterface
     */
    private $startDateTime;

    /**
     * @param string[] $tags
     */
    public function __construct(
        string $name,
        string $userGroupName,
        DateTimeInterface $startDateTime,
        Location $location,
        string $url,
        array $tags
    ) {
        $this->name = $name;
        $this->userGroupName = $userGroupName;
        $this->location = $location;
        $this->url = $url;
        $this->startDateTime = $startDateTime;
        $this->tags = $tags;
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

    public function getCoordinate(): Coordinate
    {
        return $this->location->getCoordinate();
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getUserGroupName(): string
    {
        return $this->userGroupName;
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

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
