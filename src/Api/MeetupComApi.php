<?php declare(strict_types=1);

namespace Fop\Api;

use GuzzleHttp\Client;
use Nette\Utils\Json;
use function GuzzleHttp\Psr7\build_query;
use Psr\Http\Message\ResponseInterface;

final class MeetupComApi
{
    /**
     * @var string
     */
    private const API_EVENTS_BY_GROUPS_URL = 'http://api.meetup.com/2/events';

    /**
     * @var string
     */
    private $meetupComApiKey;

    /**
     * @var Client
     */
    private $client;

    public function __construct(string $meetupComApiKey, Client $client)
    {
        $this->meetupComApiKey = $meetupComApiKey;
        $this->client = $client;
    }

    /**
     * @param int[] $groupIds
     * @return mixed[]
     */
    public function getMeetupsByGroupsIds(array $groupIds): array
    {
        $url = $this->createUrlFromGroupIds($groupIds);
        $response = $this->client->request('GET', $url);
        $result = $this->getResultFromResponse($response);

        return $result['results'];
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

    private function getResultFromResponse(ResponseInterface $response): mixed
    {
        return Json::decode($response->getBody(), Json::FORCE_ARRAY);
    }
}
