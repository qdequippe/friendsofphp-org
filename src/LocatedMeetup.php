<?php declare(strict_types=1);

namespace AllFriensOfPhp;

use DateTimeInterface;

final class LocatedMeetup
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
    private $startDateTime;

    public function getLocatoin(): Location
    {
        return $this->locatoin;
    }

    /**
     * @var Location
     */
    private $locatoin;

    public function __construct(string $name, string $userGroupName, DateTimeInterface $startDateTime, Location $locatoin)
    {
        $this->name = $name;
        $this->userGroupName = $userGroupName;
        $this->startDateTime = $startDateTime;
        $this->locatoin = $locatoin;
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
}
