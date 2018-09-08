<?php declare(strict_types=1);

namespace Fop\MeetupCom\Twig;

use Symplify\Statie\Contract\Templating\FilterProviderInterface;

final class MeetupAreaMatchFilterProvider implements FilterProviderInterface
{
    /**
     * @var string
     */
    private const LATITUDE = 'latitude';

    /**
     * @var string
     */
    private const LONGITUDE = 'longitude';

    /**
     * @return callable[]
     */
    public function provide(): array
    {
        return [
            'is_meetup_area_match' => function (array $meetup, array $area): bool {
                if ($meetup[self::LATITUDE] > $area['top_left'][self::LATITUDE]) {
                    return false;
                }

                if ($meetup[self::LATITUDE] < $area['bottom_right'][self::LATITUDE]) {
                    return false;
                }

                if ($meetup[self::LONGITUDE] > $area['bottom_right'][self::LONGITUDE]) {
                    return false;
                }

                if ($meetup[self::LONGITUDE] < $area['top_left'][self::LONGITUDE]) {
                    return false;
                }

                return true;
            },
        ];
    }
}
