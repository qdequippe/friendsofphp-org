<?php declare(strict_types=1);

namespace Fop\OpentechcalendarCoUk\Factory;

use Fop\Entity\Location;
use Fop\Entity\Meetup;
use Location\Coordinate;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

final class MeetupFactory
{
    /**
     * @param mixed[] $data
     */
    public function createFromArray(array $data): ?Meetup
    {
        // skip meetup.com events - already covered by meetup.com package
        if (Strings::match($data['url'], '#https:\/\/(www\.)?meetup.com#')) {
            return null;
        }

        // only active normal meetup
        if ($data['deleted'] || (isset($data['canceled']) && $data['canceled']) || $data['is_physical'] === false) {
            return null;
        }

        $name = $data['summary'];
        $start = DateTime::from($data['start']['rfc2882timezone']);
        $group = $data['summary'];
        $location = $this->createLocation($data);
        $link = $data['siteurl'];

        return new Meetup($name, $group, $start, $location, $link);
    }

    /**
     * @param mixed[] $data
     */
    private function createLocation(array $data): Location
    {
        $coordinate = new Coordinate((float) $data['venue']['lat'], (float) $data['venue']['lng']);

        return new Location($data['areas'][0]['title'], $data['country']['title'], $coordinate);
    }
}
