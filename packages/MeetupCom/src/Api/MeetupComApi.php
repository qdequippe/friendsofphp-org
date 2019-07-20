<?php declare(strict_types=1);

namespace Fop\MeetupCom\Api;

use Fop\Exception\ShouldNotHappenException;
use Fop\Guzzle\ResponseConverter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Strings\StringFormatConverter;

final class MeetupComApi
{
    /**
     * @see https://www.meetup.com/meetup_api/docs/:urlname/events/#list
     * @var string
     */
    private const API_EVENTS_BY_GROUPS_URL = 'http://api.meetup.com/%s/events';

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

    /**
     * @var ResponseConverter
     */
    private $responseConverter;

    public function __construct(
        string $meetupComOauthKey,
        string $meetupComOauthSecret,
        StringFormatConverter $stringFormatConverter,
        SymfonyStyle $symfonyStyle,
        ResponseConverter $responseConverter
    ) {
        $this->meetupComOauthKey = $meetupComOauthKey;
        $this->meetupComOauthSecret = $meetupComOauthSecret;
        $this->stringFormatConverter = $stringFormatConverter;
        $this->symfonyStyle = $symfonyStyle;
        $this->responseConverter = $responseConverter;
    }

    /**
     * @param string[] $groupSlugs
     * @return mixed[]
     */
    public function getMeetupsByGroupSlugs(array $groupSlugs): array
    {
        $meetups = [];
        $errors = [];

        $progressBar = $this->symfonyStyle->createProgressBar(count($groupSlugs));
        $client = $this->createOauth2AwareHttpClient();

        foreach ($groupSlugs as $groupSlug) {
            $url = sprintf(self::API_EVENTS_BY_GROUPS_URL, $groupSlug);

            $progressBar->advance();

            try {
                $response = $client->request('GET', $url);
            } catch (GuzzleException $guzzleException) {
                // the group might not exists anymore, but it should not be a blocker for existing groups
                $errors[] = $guzzleException->getMessage();
                continue;
            }

            $json = $this->responseConverter->toJson($response);
            if ($json === []) {
                continue;
            }

            $meetups = array_merge($meetups, $json);
        }

        foreach ($errors as $error) {
            $this->symfonyStyle->error($error);
        }

        return $meetups;
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
