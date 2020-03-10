<?php

declare(strict_types=1);

namespace Fop\ValueObject;

use DateTimeInterface;
use Fop\Utils\DateStaticUtils;

final class Group
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $meetupComSlug;

    /**
     * @var string
     */
    private $country;

    /**
     * @var DateTimeInterface|null
     */
    private $lastMeetupDateTime;

    public function __construct(
        string $name,
        string $meetupComSlug,
        string $country,
        ?DateTimeInterface $lastMeetupDateTime
    ) {
        $this->name = $name;
        $this->meetupComSlug = $meetupComSlug;
        $this->country = $country;
        $this->lastMeetupDateTime = $lastMeetupDateTime;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMeetupComSlug(): string
    {
        return $this->meetupComSlug;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getDaysFromLastMeetup(): ?int
    {
        return DateStaticUtils::getDiffFromTodayInDays($this->lastMeetupDateTime);
    }

    public function changeLastMeetupDateTime(?DateTimeInterface $lastMeetupDateTime): void
    {
        $this->lastMeetupDateTime = $lastMeetupDateTime;
    }

    public function getLastMeetupDateTime(): ?DateTimeInterface
    {
        return $this->lastMeetupDateTime;
    }

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'meetup_com_slug' => $this->getMeetupComSlug(),
            'country' => $this->country,
        ];

        if ($this->lastMeetupDateTime) {
            $data['last_meetup_date_time'] = $this->lastMeetupDateTime->format('Y-m-d');
        } else {
            $data['last_meetup_date_time'] = null;
        }

        return $data;
    }
}
