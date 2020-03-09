<?php declare(strict_types=1);

namespace Fop\ValueObject;

use DateTimeInterface;
use Fop\Utils\DateStaticUtils;

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

    public function getStartInDays(): ?int
    {
        return DateStaticUtils::getDiffFromTodayInDays($this->startDateTime);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'user_group_name' => $this->userGroupName,
            'start_date_time' => $this->startDateTime->format('Y-m-d H:i'),
            'city' => $this->getCity(),
            'country' => $this->getCountry(),
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
            'url' => $this->url,
        ];
    }
}
