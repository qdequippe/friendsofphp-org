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
     * @var DateTimeInterface
     */
    private $dateTime;

    public function getLocatoin(): Location
    {
        return $this->location;
    }

    /**
     * @var Location
     */
    private $location;

    public function __construct(
        string $name,
        string $userGroupName,
        DateTimeInterface $dateTime,
        Location $location
    ) {
        $this->name = $name;
        $this->userGroupName = $userGroupName;
        $this->dateTime = $dateTime;
        $this->location = $location;
    }

    public function getUserGroupName(): string
    {
        return $this->userGroupName;
    }

    public function getDateTime(): DateTimeInterface
    {
        return $this->dateTime;
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
