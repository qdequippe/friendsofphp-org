<?php declare(strict_types=1);

namespace Fop\MeetupCom\Api;

use Fop\Exception\ShouldNotHappenException;
use GuzzleHttp\Client;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;
use Nette\Utils\Json;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Strings\StringFormatConverter;

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

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(
        string $meetupComOauthKey,
        string $meetupComOauthSecret,
        StringFormatConverter $stringFormatConverter,
        SymfonyStyle $symfonyStyle
    ) {
        $this->meetupComOauthKey = $meetupComOauthKey;
        $this->meetupComOauthSecret = $meetupComOauthSecret;
        $this->stringFormatConverter = $stringFormatConverter;
        $this->symfonyStyle = $symfonyStyle;
    }

    /**
     * @param string[] $groupSlugs
     * @return mixed[]
     */
    public function getMeetupsByGroupSlugs(array $groupSlugs): array
    {
        $meetups = [];

        $progressBar = $this->symfonyStyle->createProgressBar(count($groupSlugs));

        foreach ($groupSlugs as $groupSlug) {
            $url = $this->createUrlFromGroupSlug($groupSlug);

            $progressBar->advance();

            $client = $this->createOauth2AwareHttpClient();
            $response = $client->request('GET', $url);
            $json = $this->getJsonFromResponse($response);

            $meetups = array_merge($meetups, $json['results'] ?? []);
        }

        return $meetups;
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

    private function createUrlFromGroupSlug(string $groupSlug): string
    {
        # https://www.meetup.com/meetup_api/docs/:urlname/events/#list
        return sprintf(self::API_EVENTS_BY_GROUPS_URL, $groupSlug);
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

    private function ensureOAuthKeysAreSet(): void
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
