<?php declare(strict_types=1);

namespace Fop\Meetup\ValueObject;

use DateTimeInterface;
use Fop\Core\Utils\DateStaticUtils;
use Fop\Meetup\Contract\ArrayableInterface;
use Location\Coordinate;

final class Meetup implements ArrayableInterface
{
    public function __construct(
        private string $name,
        private string $userGroupName,
        private DateTimeInterface $startDateTime,
        private string $url,
        private string $city,
        private string $country,
        private float $latitude,
        private float $longitude
    ) {
    }

    public function getLocation(): Location
    {
        $coordinate = new Coordinate($this->latitude, $this->longitude);
        return new Location($this->city, $this->country, $coordinate);
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): string
    {
        return $this->country;
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
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
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
