<?php

declare(strict_types=1);

namespace Fop\MeetupCom\Api;

use DateTimeInterface;
use Fop\Guzzle\ResponseConverter;
use Fop\MeetupCom\Guzzle\Oauth2AwareClient;
use Nette\Utils\DateTime;

final class MeetupComApi
{
    /**
     * @see https://www.meetup.com/meetup_api/docs/:urlname/events/#list
     * @var string
     */
    private const API_EVENTS_BY_GROUPS_URL = 'https://api.meetup.com/%s/events';

    /**
     * @see https://www.meetup.com/meetup_api/docs/:urlname/events/#list
     * @var string
     */
    private const API_LAST_EVENTS_BY_GROUPS_URL = 'https://api.meetup.com/%s/events?status=past&desc=true&page=1';

    public function __construct(
        private readonly ResponseConverter $responseConverter,
        private readonly Oauth2AwareClient $oauth2AwareClient
    ) {
    }

    /**
     * @return mixed[]
     */
    public function getMeetupsByGroupSlug(string $groupSlug): array
    {
        $url = sprintf(self::API_EVENTS_BY_GROUPS_URL, $groupSlug);

        $response = $this->oauth2AwareClient->get($url);
        return $this->responseConverter->toJson($response);
    }

    public function getLastMeetupDateTimeByGroupSlug(string $groupSlug): ?DateTimeInterface
    {
        $url = sprintf(self::API_LAST_EVENTS_BY_GROUPS_URL, $groupSlug);

        $response = $this->oauth2AwareClient->get($url);
        $data = $this->responseConverter->toJson($response);

        if (! isset($data[0])) {
            return null;
        }

        return DateTime::from($data[0]['local_date']);
    }
}
