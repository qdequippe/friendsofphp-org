<?php declare(strict_types=1);

namespace AllFriensOfPhp;

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

    public function __construct(string $name, string $userGroupName, DateTimeInterface $startDateTime)
    {
        $this->name = $name;
        $this->userGroupName = $userGroupName;
        $this->startDateTime = $startDateTime;
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
