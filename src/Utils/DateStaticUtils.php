<?php

declare(strict_types=1);

namespace Fop\Utils;

use DateInterval;
use DateTimeInterface;
use Nette\Utils\DateTime;

final class DateStaticUtils
{
    public static function getDiffFromTodayInDays(DateTimeInterface $dateTime): int
    {
        /** @var DateInterval $dateInterval */
        $dateInterval = $dateTime->diff(new DateTime('now'));
        if ($dateInterval->invert === 0) {
            return -$dateInterval->days;
        }

        return (int) $dateInterval->days;
    }
}
