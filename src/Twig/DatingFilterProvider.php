<?php declare(strict_types=1);

namespace Fop\Twig;

use Nette\Utils\DateTime;
use Symplify\Statie\Contract\Templating\FilterProviderInterface;

final class DatingFilterProvider implements FilterProviderInterface
{
    /**
     * @return callable[]
     */
    public function provide(): array
    {
        return [
            'diffFromTodayInDays' => function (string $dateTime): int {
                $dateTime = new DateTime($dateTime);
                $dateInterval = $dateTime->diff(new DateTime('now'));
                if (! $dateInterval->invert) {
                    return (int) - $dateInterval->days;
                }

                return (int) $dateInterval->days;
            },
        ];
    }
}
