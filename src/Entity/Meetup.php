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
     * @var TimeSpan
     */
    private $timeSpan;

    /**
     * @var Location
     */
    private $location;

    public function __construct(
        string $name,
        string $userGroupName,
        TimeSpan $timeSpan,
        Location $location,
        string $url
    ) {
        $this->name = $name;
        $this->userGroupName = $userGroupName;
        $this->location = $location;
        $this->url = $url;
        $this->timeSpan = $timeSpan;
    }

    public function getLocatoin(): Location
    {
        return $this->location;
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
        return $this->timeSpan->getStartDateTime();
    }

    public function getEndDateTime(): ?DateTimeInterface
    {
        return $this->timeSpan->getEndDateTime();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUserGroup(): string
    {
        return $this->userGroupName;
    }

    public function getLongitude(): float
    {
        return $this->location->getLongitude();
    }

    public function getLatitude(): float
    {
        return $this->location->getLatitude();
    }
}
