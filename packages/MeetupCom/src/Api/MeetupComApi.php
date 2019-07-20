<?php declare(strict_types=1);

namespace Fop\MeetupCom\Api;

use Fop\Exception\ShouldNotHappenException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;
use Nette\Utils\Json;
use Psr\Http\Message\ResponseInterface;
use ReflectionProperty;
use Symplify\PackageBuilder\Strings\StringFormatConverter;
use function GuzzleHttp\Psr7\build_query;

final class MeetupComApi
{
    /**
     * @var string
     */
    private const API_EVENTS_BY_GROUPS_URL = 'http://api.meetup.com/%s/events';

    /**
     * e.g. http://api.meetup.com/dallasphp
     * @var string
     */
    private const API_GROUP_DETAIL_URL = 'http://api.meetup.com/';

    /**
     * @var string
     */
    private $meetupComOauthKey;

    /**
     * @var string
     */
    private $meetupComOauthSecret;
    /**
     * @var StringFormatConverter
     */
    private $stringFormatConverter;

    public function __construct(string $meetupComOauthKey, string $meetupComOauthSecret, StringFormatConverter $stringFormatConverter)
    {
        $this->meetupComOauthKey = $meetupComOauthKey;
        $this->meetupComOauthSecret = $meetupComOauthSecret;
        $this->stringFormatConverter = $stringFormatConverter;
    }

    /**
     * @param int[] $groupIds
     * @return mixed[]
     */
    public function getMeetupsByGroupsIds(array $groupIds): array
    {
        $url = $this->createUrlFromGroupIds($groupIds);

        $client = $this->createOauth2AwareHttpClient();
        $response = $client->request('GET', $url);
        $json = $this->getJsonFromResponse($response);

        return $json['results'] ?? [];
    }

    /**
     * @return mixed[]
     */
    public function getGroupForUrl(string $url): array
    {
        $url = self::API_GROUP_DETAIL_URL . $this->resolveGroupUrlNameFromGroupUrl($url);

        $client = $this->createOauth2AwareHttpClient();
        $response = $client->request('GET', $url);

        return $this->getJsonFromResponse($response);
    }

    /**
     * @param int[] $groupIds
     */
    private function createUrlFromGroupIds(array $groupIds): string
    {
        $groupIdsAsString = implode(',', $groupIds);

        # https://www.meetup.com/meetup_api/docs/:urlname/events/#list
        return self::API_EVENTS_BY_GROUPS_URL . '?' . build_query([
            # https://www.meetup.com/meetup_api/docs/2/events/#params
            'group_id' => $groupIdsAsString,
            # https://www.meetup.com/meetup_api/auth/#keys
        ]);
    }

    /**
     * @see https://github.com/kamermans/guzzle-oauth2-subscriber#middleware-guzzle-6
     */
    private function createOauth2AwareHttpClient(): Client
    {
        $this->ensureOAuthKeysAreSet();

        $reauthClient = new Client([
            // URL for access_token request
            'base_uri' => 'https://secure.meetup.com/oauth2/access',
        ]);

        $reauthConfig = [
            'client_id' => $this->meetupComOauthKey,
            'client_secret' => $this->meetupComOauthSecret,
        ];

        $clientCredentials = new ClientCredentials($reauthClient, $reauthConfig);
        $oAuth2Middleware = new OAuth2Middleware($clientCredentials);

        $client = new Client();
        $client->getConfig('handler')->push($oAuth2Middleware);

        return $client;
    }

    /**
     * @return mixed[]
     */
    private function getJsonFromResponse(ResponseInterface $response): array
    {
        return Json::decode((string) $response->getBody(), Json::FORCE_ARRAY);
    }

    private function resolveGroupUrlNameFromGroupUrl(string $url): string
    {
        $url = rtrim($url, '/');
        $array = explode('/', $url);
        return $array[count($array) - 1];
    }

    private function ensureOAuthKeysAreSet()
    {
        if ($this->meetupComOauthKey === 'empty') {
            $envValueName = $this->convertPropertyNameToEnvName('meetupComOauthKey');

            throw new ShouldNotHappenException(sprintf(
                'Env "%s" is needed to run this. Add it to CI or "%s=VALUE bin/console ..."',
                $envValueName,
                $envValueName
            ));
        }

        if ($this->meetupComOauthSecret === 'empty') {
            $envValueName = $this->convertPropertyNameToEnvName('meetupComOauthSecret');

            throw new ShouldNotHappenException(sprintf(
                'Env "%s" is needed to run this. Add it to CI or "%s=VALUE bin/console ..."',
                $envValueName,
                $envValueName
            ));
        }
    }

    private function convertPropertyNameToEnvName(string $name): string
    {
        $underscore = $this->stringFormatConverter->camelCaseToUnderscore($name);

        return strtoupper($underscore);
    }
}
