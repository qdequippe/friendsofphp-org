<?php declare(strict_types=1);

namespace Fop\Nomad\ValueObject;

use DateTimeInterface;

final class TimeSpan
{
    /**
     * @var DateTimeInterface
     */
    private $startDateTime;

    /**
     * @var DateTimeInterface
     */
    private $endDateTime;

    public function __construct(DateTimeInterface $startDateTime, DateTimeInterface $endDateTime)
    {
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
    }

    public function getStartDateTime(): DateTimeInterface
    {
        return $this->startDateTime;
    }

    public function getEndDateTime(): DateTimeInterface
    {
        return $this->endDateTime;
    }

    public function containsDateTime(DateTimeInterface $dateTime): bool
    {
        return $dateTime > $this->startDateTime && $dateTime < $this->endDateTime;
    }
}
