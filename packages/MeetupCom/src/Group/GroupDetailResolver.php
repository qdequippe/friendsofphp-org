<?php declare(strict_types=1);

namespace Fop\MeetupCom\Group;

use Fop\Geolocation\Geolocator;
use Fop\MeetupCom\Api\MeetupComApi;
use Fop\MeetupCom\Exception\InvalidGroupUrlException;
use Nette\Utils\Strings;
use Nette\Utils\Validators;
use function Safe\sprintf;

final class GroupDetailResolver
{
    /**
     * @var MeetupComApi
     */
    private $meetupComApi;

    /**
     * @var Geolocator
     */
    private $geolocator;

    public function __construct(MeetupComApi $meetupComApi, Geolocator $geolocator)
    {
        $this->meetupComApi = $meetupComApi;
        $this->geolocator = $geolocator;
    }

    /**
     * @return mixed[]
     */
    public function resolveFromUrl(string $url): array
    {
        $this->ensureIsUrl($url);

        $group = $this->meetupComApi->getGroupForUrl($url);
        $group['country'] = $this->geolocator->resolveCountryByGroup($group);

        return $group;
    }

    private function ensureIsUrl(string $url): void
    {
        if (Validators::isUrl($url) && Strings::match($url, '#http(s)?://(www\.)?meetup.com#')) {
            return;
        }

        throw new InvalidGroupUrlException(sprintf(
            '"%s" is not valid meetup url. Provide correct one like "%s"',
            $url,
            'https://www.meetup.com/viennaphp/'
        ));
    }
}
