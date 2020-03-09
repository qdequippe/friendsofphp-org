<?php declare(strict_types=1);

namespace Fop\MeetupCom\Api;

use DateTimeInterface;
use Fop\Guzzle\ResponseConverter;
use Fop\MeetupCom\Guzzle\Oauth2AwareClient;
use Fop\MeetupCom\ValueObject\RateLimits;
use Nette\Utils\DateTime;

final class MeetupComApi
{
    /**
     * @see https://www.meetup.com/meetup_api/docs/:urlname/events/#list
     * @var string
     */
    private const API_EVENTS_BY_GROUPS_URL = 'http://api.meetup.com/%s/events';

    /**
     * @see https://www.meetup.com/meetup_api/docs/status/
     * @var string
     */
    private const STATUS_URL = 'http://api.meetup.com/status';

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
     * Probably bit older, but @see https://github.com/jkutianski/meetup-api/wiki#limits
     */
    public function getRateLimits(): RateLimits
    {
        $response = $this->oauth2AwareClient->request('GET', self::STATUS_URL);

        $limit = $response->getHeader('X-RateLimit-Limit')[0];
        $remaining = $response->getHeader('X-RateLimit-Remaining')[0];
        $reset = $response->getHeader('X-RateLimit-Reset')[0];

        return new RateLimits((int) $limit, (int) $remaining, (int) $reset);
    }

    public function getRemainingRequestCount(): int
    {
        $rateLimits = $this->getRateLimits();
        return $rateLimits->getRemainingRequests();
    }

    public function getLastMeetupDateTimeByGroupSlug(string $groupSlug): ?DateTimeInterface
    {
        $url = sprintf(self::API_LAST_EVENTS_BY_GROUPS_URL, $groupSlug);

        $response = $this->oauth2AwareClient->request('GET', $url);
        $data = $this->responseConverter->toJson($response);

        if (! isset($data[0])) {
            return null;
        }

        return DateTime::from($data[0]['local_date']);
    }
}
