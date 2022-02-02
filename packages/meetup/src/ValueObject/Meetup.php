<?php

declare(strict_types=1);

namespace Fop\Meetup\ValueObject;

use DateTimeInterface;
use Fop\Core\Utils\DateStaticUtils;
use Fop\Meetup\Contract\ArrayableInterface;
use Nette\Utils\DateTime;

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

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['user_group_name'],
            DateTime::from($data['start_date_time']),
            $data['url'],
            $data['city'],
            $data['country'],
            $data['latitude'],
            $data['longitude']
        );
    }

    /**
     * @api used in twig
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @api used in twig
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @api used in twig
     */
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

    /**
     * @api used in twig
     */
    public function getUserGroup(): string
    {
        return $this->userGroupName;
    }

    /**
     * @api used in twig
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @api used in twig
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @api used in twig
     */
    public function getStartInDays(): int
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
            'city' => $this->city,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'url' => $this->url,
        ];
    }
}
