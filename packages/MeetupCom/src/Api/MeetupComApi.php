<?php declare(strict_types=1);

namespace Fop\MeetupCom\Api;

use GuzzleHttp\Exception\ClientException;
use Symplify\PackageBuilder\Http\BetterGuzzleClient;
use function GuzzleHttp\Psr7\build_query;

final class MeetupComApi
{
    /**
     * @var string
     */
    private const API_EVENTS_BY_GROUPS_URL = 'http://api.meetup.com/2/events';

    /**
     * e.g. http://api.meetup.com/dallasphp
     * @var string
     */
    private const API_GROUP_DETAIL_URL = 'http://api.meetup.com/';

    /**
     * @var string
     */
    private $meetupComApiKey;

    /**
     * @var BetterGuzzleClient
     */
    private $betterGuzzleClient;

    public function __construct(string $meetupComApiKey, BetterGuzzleClient $betterGuzzleClient)
    {
        $this->meetupComApiKey = $meetupComApiKey;
        $this->betterGuzzleClient = $betterGuzzleClient;
    }

    /**
     * @param int[] $groupIds
     * @return mixed[]
     */
    public function getMeetupsByGroupsIds(array $groupIds): array
    {
        $url = $this->createUrlFromGroupIds($groupIds);

        $json = $this->betterGuzzleClient->requestToJson($url);

        return $json['results'] ?? [];
    }

    public function getIdForGroupUrl(string $url): ?int
    {
        try {
            return $this->getGroupForUrl($url)['id'];
        } catch (ClientException $clientException) {
            if (in_array($clientException->getCode(), [404, 410], true)) {
                // 404: the group is not accessible
                // 410: the group doesn't exist anymore
                return null;
            }

            // other unknown error, show it
            throw $clientException;
        }
    }

    /**
     * @return mixed[]
     */
    public function getGroupForUrl(string $url): array
    {
        $url = self::API_GROUP_DETAIL_URL . $this->resolveGroupUrlNameFromGroupUrl($url);

        return $this->betterGuzzleClient->requestToJson($url);
    }

    /**
     * @param int[] $groupIds
     */
    private function createUrlFromGroupIds(array $groupIds): string
    {
        $groupIdsAsString = implode(',', $groupIds);

        return self::API_EVENTS_BY_GROUPS_URL . '?' . build_query([
            # https://www.meetup.com/meetup_api/docs/2/events/#params
            'group_id' => $groupIdsAsString,
            # https://www.meetup.com/meetup_api/auth/#keys
            'key' => $this->meetupComApiKey,
        ]);
    }

    private function resolveGroupUrlNameFromGroupUrl(string $url): string
    {
        $url = rtrim($url, '/');
        $array = explode('/', $url);
        return $array[count($array) - 1];
    }
}
