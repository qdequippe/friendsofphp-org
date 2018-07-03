<?php declare(strict_types=1);

namespace Fop;

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
    private $startDateTime;

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
        DateTimeInterface $startDateTime,
        Location $location
    ) {
        $this->name = $name;
        $this->userGroupName = $userGroupName;
        $this->startDateTime = $startDateTime;
        $this->location = $location;
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
