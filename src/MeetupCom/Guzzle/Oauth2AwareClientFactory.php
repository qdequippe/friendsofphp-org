<?php

declare(strict_types=1);

namespace Fop\MeetupCom\Guzzle;

use Fop\Exception\ShouldNotHappenException;
use Fop\ValueObject\Option;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\PackageBuilder\Strings\StringFormatConverter;

/**
 * @api
 */
final class Oauth2AwareClientFactory
{
    private readonly string $meetupComOauthKey;

    private readonly string $meetupComOauthSecret;

    public function __construct(
        ParameterProvider $parameterProvider,
        private readonly StringFormatConverter $stringFormatConverter
    ) {
        $this->meetupComOauthKey = $parameterProvider->provideStringParameter(Option::MEETUP_COM_OAUTH_KEY);
        $this->meetupComOauthSecret = $parameterProvider->provideStringParameter(Option::MEETUP_COM_OAUTH_SECRET);
    }

    /**
     * @api Use in factory in config
     * @see https://github.com/kamermans/guzzle-oauth2-subscriber#middleware-guzzle-6
     */
    public function create(): Oauth2AwareClient
    {
        $this->ensureOAuthKeysAreSet();

        $reauthClient = new Client([
            // URL for access_token request
            'base_uri' => 'https://secure.meetup.com/oauth2/access',
        ]);

        $clientCredentials = new ClientCredentials($reauthClient, [
            'client_id' => $this->meetupComOauthKey,
            'client_secret' => $this->meetupComOauthSecret,
        ]);

        $oAuth2Middleware = new OAuth2Middleware($clientCredentials);

        return $this->decorateWithOauth2Client($oAuth2Middleware);
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

    private function decorateWithOauth2Client(OAuth2Middleware $oAuth2Middleware): Oauth2AwareClient
    {
        $handlerStack = HandlerStack::create();
        $handlerStack->push($oAuth2Middleware);

        return new Oauth2AwareClient([
            'handler' => $handlerStack,
        ]);
    }
}
