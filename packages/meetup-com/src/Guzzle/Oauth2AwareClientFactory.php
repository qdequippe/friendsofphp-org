<?php declare(strict_types=1);

namespace Fop\MeetupCom\Guzzle;

use Fop\Core\Exception\ShouldNotHappenException;
use GuzzleHttp\Client;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;
use Symplify\PackageBuilder\Strings\StringFormatConverter;

final class Oauth2AwareClientFactory
{
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

    public function __construct(
        string $meetupComOauthKey,
        string $meetupComOauthSecret,
        StringFormatConverter $stringFormatConverter
    ) {
        $this->meetupComOauthKey = $meetupComOauthKey;
        $this->meetupComOauthSecret = $meetupComOauthSecret;
        $this->stringFormatConverter = $stringFormatConverter;
    }

    /**
     * @see https://github.com/kamermans/guzzle-oauth2-subscriber#middleware-guzzle-6
     */
    public function create(): Oauth2AwareClient
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

        $client = new Oauth2AwareClient();
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
