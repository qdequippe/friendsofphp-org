<?php declare(strict_types=1);

namespace Fop\MeetupCom\Api;

use Fop\Guzzle\ResponseFormatter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
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
     * @var Client
     */
    private $client;

    /**
     * @var ResponseFormatter
     */
    private $responseFormatter;

    public function __construct(string $meetupComApiKey, Client $client, ResponseFormatter $responseFormatter)
    {
        $this->meetupComApiKey = $meetupComApiKey;
        $this->client = $client;
        $this->responseFormatter = $responseFormatter;
    }

    /**
     * @param int[] $groupIds
     * @return mixed[]
     */
    public function getMeetupsByGroupsIds(array $groupIds): array
    {
        $url = $this->createUrlFromGroupIds($groupIds);
        $response = $this->client->request('GET', $url);

        return $this->responseFormatter->formatResponseToJson($response, $url)['results'];
    }

    public function getIdForGroupUrl(string $url): ?int
    {
        try {
            $group = $this->getGroupForUrl($url);

            return $group['id'];
        } catch (ClientException $clientException) {
            if (in_array($clientException->getCode(), [410, 404], true)) {
                // 410: the group is not accessible
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
        $groupPart = $this->resolveGroupUrlNameFromGroupUrl($url);

        $url = self::API_GROUP_DETAIL_URL . $groupPart;
        $response = $this->client->request('get', $url);
        return $this->responseFormatter->formatResponseToJson($response, $url);
    }

    /**
     * @return mixed[]
     */
    public function findMeetupsGroupsByKeyword(string $keyword): array
    {
        $url = sprintf('http://api.meetup.com/topics?search=%s&only=id,name&key=%s', $keyword, $this->meetupComApiKey);
        $response = $this->client->request('GET', $url);

        return $this->responseFormatter->formatResponseToJson($response, $url)['results'];
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
