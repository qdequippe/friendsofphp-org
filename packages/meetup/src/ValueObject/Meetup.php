<?php declare(strict_types=1);

namespace Fop\Meetup\ValueObject;

use DateTimeInterface;
use Fop\Core\Utils\DateStaticUtils;
use Fop\Meetup\Contract\ArrayableInterface;

final class Meetup implements ArrayableInterface
{
    public function __construct(
        private string $name,
        private string $userGroupName,
        private DateTimeInterface $startDateTime,
        private Location $location,
        private string $url
    ) {
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

    public function getStartDateTimeFormatted(string $format): string
    {
        return $this->startDateTime->format($format);
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
        return $this->location->getCoordinateLatitude();
    }

    public function getLongitude(): float
    {
        return $this->location->getCoordinateLongitude();
    }

    public function getStartInDays(): ?int
    {
        return DateStaticUtils::getDiffFromTodayInDays($this->startDateTime);
    }

    /**
     * @return array<string, mixed>
     */
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
