<?php declare(strict_types=1);

namespace Fop\MeetupCom\Twig;

use Symplify\Statie\Contract\Templating\FilterProviderInterface;

final class MeetupAreaFilterProvider implements FilterProviderInterface
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
     * @var mixed[]
     */
    private $areas = [];

    /**
     * @param mixed[] $areas
     */
    public function __construct(array $areas)
    {
        $this->areas = $areas;
    }

    /**
     * @return callable[]
     */
    public function provide(): array
    {
        return [
            'detect_meetup_area' => function (array $meetup): string {
                foreach ($this->areas as $area) {
                    if ($this->isAreaMatch($meetup, $area)) {
                        return $area['key'];
                    }
                }

                return 'other';
            },
        ];
    }

    /**
     * @param mixed[] $meetup
     * @param mixed[] $area
     */
    private function isAreaMatch(array $meetup, array $area): bool
    {
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
    }
}
