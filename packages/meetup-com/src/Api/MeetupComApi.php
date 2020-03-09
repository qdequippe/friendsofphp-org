<?php declare(strict_types=1);

namespace Fop\MeetupCom\Api;

use Fop\Guzzle\ResponseConverter;
use Fop\MeetupCom\Guzzle\Oauth2AwareClient;

final class MeetupComApi
{
    /**
     * @see https://www.meetup.com/meetup_api/docs/:urlname/events/#list
     * @var string
     */
    private const API_EVENTS_BY_GROUPS_URL = 'http://api.meetup.com/%s/events';

    /**
     * @see https://www.meetup.com/meetup_api/docs/:urlname/events/#list
     * @var string
     */
    private const API_LAST_EVENTS_BY_GROUPS_URL = 'http://api.meetup.com/%s/events?status=past&desc=true&page=1';

    /**
     * @var ResponseConverter
     */
    private $responseConverter;

    /**
     * @var Oauth2AwareClient
     */
    private $oauth2AwareClient;

    public function __construct(ResponseConverter $responseConverter, Oauth2AwareClient $oauth2AwareClient)
    {
        $this->responseConverter = $responseConverter;
        $this->oauth2AwareClient = $oauth2AwareClient;
    }

    /**
     * @return mixed[]
     */
    public function getMeetupsByGroupSlug(string $groupSlug): array
    {
        $url = sprintf(self::API_EVENTS_BY_GROUPS_URL, $groupSlug);

        $response = $this->oauth2AwareClient->request('GET', $url);

        return $this->responseConverter->toJson($response);
    }

    /**
     * @return mixed[]|null
     */
    public function getLastMeetupByGroupSlug(string $groupSlug): ?array
    {
        $url = sprintf(self::API_LAST_EVENTS_BY_GROUPS_URL, $groupSlug);

        $response = $this->oauth2AwareClient->request('GET', $url);

        $data = $this->responseConverter->toJson($response);

        return $data[0] ?? null;
    }
}
