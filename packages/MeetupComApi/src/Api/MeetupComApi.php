<?php declare(strict_types=1);

namespace Fop\MeetupComApi\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Nette\Utils\Json;
use Psr\Http\Message\ResponseInterface;
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

    public function getIdForGroupUrl(string $url): ?int
    {
        try {
            $group = $this->getGroupForUrl($url);

            return $group['id'];
        } catch (ClientException $clientException) {
            if ($clientException->getCode() === 404) {
                // the group doesn't exist anymore, skip it
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

        $response = $this->client->request('get', self::API_GROUP_DETAIL_URL . $groupPart);

        return $this->getResultFromResponse($response);
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

    /**
     * @return mixed
     */
    private function getResultFromResponse(ResponseInterface $response)
    {
        return Json::decode($response->getBody(), Json::FORCE_ARRAY);
    }

    private function resolveGroupUrlNameFromGroupUrl(string $url): string
    {
        $url = rtrim($url, '/');

        $array = explode('/', $url);

        return $array[count($array) - 1];
    }
}
