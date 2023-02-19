<?php

declare(strict_types=1);

namespace Fop\Meetup\ValueObject;

use DateTimeInterface;
use Fop\Meetup\Contract\ArrayableInterface;
use Fop\Utils\DateStaticUtils;
use Nette\Utils\DateTime;
use Stringable;

/**
 * @api used in twig
 */
final class Meetup implements ArrayableInterface, Stringable
{
    public function __construct(
        private readonly string $name,
        private readonly string $userGroupName,
        private readonly DateTimeInterface $utcStartDateTime,
        private readonly string $localDate,
        private readonly string $localTime,
        private readonly string $url,
        private readonly string $city,
        private readonly string $country,
        private readonly float $latitude,
        private readonly float $longitude,
    ) {
    }

    /**
     * Unique group time stamp and id, for filtering unique meetups.
     */
    public function __toString(): string
    {
        return $this->userGroupName . '_' . $this->name . '_' . $this->utcStartDateTime->format('Y-m-d');
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['user_group_name'],
            DateTime::from($data['utc_start_date_time']),
            $data['local_date'],
            $data['local_time'],
            $data['url'],
            $data['city'],
            $data['country'],
            $data['latitude'],
            $data['longitude'],
        );
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

    public function getUtcStartDateTime(): DateTimeInterface
    {
        return $this->utcStartDateTime;
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

    public function getStartInDays(): int
    {
        return DateStaticUtils::getDiffFromTodayInDays($this->utcStartDateTime);
    }

    /**
     * @return array{name: string, user_group_name: string, local_date: string, local_time: string, utc_start_date_time: string, city: string, country: string, latitude: float, longitude: float, url: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'user_group_name' => $this->userGroupName,
            'local_date' => $this->localDate,
            'local_time' => $this->localTime,
            'utc_start_date_time' => $this->utcStartDateTime->format('U'),
            'city' => $this->city,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'url' => $this->url,
        ];
    }

    public function getLocalDate(): string
    {
        return $this->localDate;
    }

    public function getLocalTime(): string
    {
        return $this->localTime;
    }
}
