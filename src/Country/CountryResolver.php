<?php declare(strict_types=1);

namespace Fop\Country;

use Fop\Guzzle\ResponseFormatter;
use GuzzleHttp\Client;
use Rinvex\Country\CountryLoader;
use Throwable;

final class CountryResolver
{
    /**
     * @var string
     */
    private const UNKNOWN_COUNTRY = 'unknown';

    /**
     * @var string
     */
    private const API_LOCATION_TO_COUNTRY = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=%s&lon=%s';

    /**
     * @var string[]
     */
    private $cachedCountryCodeByLatitudeAndLongitude = [];

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ResponseFormatter
     */
    private $responseFormatter;

    /**
     * @var string[]
     */
    private $usaStates = [];

    /**
     * @param string[] $usaStates
     */
    public function __construct(Client $client, ResponseFormatter $responseFormatter, array $usaStates)
    {
        $this->client = $client;
        $this->responseFormatter = $responseFormatter;
        $this->usaStates = $usaStates;
    }

    /**
     * @param mixed[] $group
     */
    public function resolveFromGroup(array $group): string
    {
        // Special case for USA, since there are many federate states
        if (isset($group['country']) && $group['country'] === 'US') {
            $stateCode = strtolower($group['state']);

            if (isset($this->usaStates[$stateCode])) {
                return $this->usaStates[$stateCode];
            }

            // unknown state :(, fallback
        }

        $countryCode = $this->resolveCountryCodeFromGroup($group);
        if ($countryCode === null) {
            return self::UNKNOWN_COUNTRY;
        }

        try {
            $countryOrCountries = CountryLoader::country($countryCode);
            if (is_array($countryOrCountries)) {
                $country = array_pop($countryOrCountries);
            } else {
                $country = $countryOrCountries;
            }

            return $country->getName();
        } catch (Throwable $throwable) {
            return self::UNKNOWN_COUNTRY;
        }
    }

    /**
     * @param mixed[] $group
     */
    private function resolveCountryCodeFromGroup(array $group): ?string
    {
        if (isset($group['country']) && $group['country'] && $group['country'] !== '-') {
            return $group['country'];
        }

        if (! isset($group['latitude'], $group['longitude'])) {
            return null;
        }

        return $this->getCountryCodeByLatitudeAndLongitude($group['latitude'], $group['longitude']);
    }

    /**
     * @see https://stackoverflow.com/a/45826290/1348344
     */
    private function getCountryCodeByLatitudeAndLongitude(float $latitude, float $longitude): ?string
    {
        $cacheKey = sha1($longitude . $longitude);
        if (isset($this->cachedCountryCodeByLatitudeAndLongitude[$cacheKey])) {
            return $this->cachedCountryCodeByLatitudeAndLongitude[$cacheKey];
        }

        $url = sprintf(self::API_LOCATION_TO_COUNTRY, $latitude, $longitude);

        $response = $this->client->request('GET', $url);
        // unable to resolve country
        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $json = $this->responseFormatter->formatResponseToJson($response, $url);

        $countryCode = $json['address']['country_code'];

        $this->cachedCountryCodeByLatitudeAndLongitude[$cacheKey] = $countryCode;

        return $countryCode;
    }
}
