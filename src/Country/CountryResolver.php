<?php declare(strict_types=1);

namespace Fop\Country;

use Fop\Guzzle\ResponseFormatter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
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
     * @var mixed[]
     */
    private $countryJsonByLatitudeAndLongitudeCache = [];

    /**
     * @var string[]
     */
    private $usaStates = [];

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ResponseFormatter
     */
    private $responseFormatter;

    /**
     * @param string[] $usaStates
     */
    public function __construct(Client $client, ResponseFormatter $responseFormatter, array $usaStates)
    {
        $this->client = $client;
        $this->responseFormatter = $responseFormatter;
        $this->usaStates = $usaStates;
    }

    public function resolveByLatitudeAndLongitude(float $latitude, float $longitude): string
    {
        $countryJson = $this->getCountryJsonByLatitudeAndLongitude($latitude, $longitude);

        if ($countryJson['address']['country'] === 'USA') {
            return $countryJson['address']['state'];
        }

        return $countryJson['address']['country'];
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
     * @param mixed[] $venue
     */
    public function resolveByVenue(array $venue): string
    {
        if ($venue['localized_country_name'] !== 'USA') {
            return $venue['localized_country_name'];
        }

        if (isset($venue['state'])) {
            $stateCode = strtolower($venue['state']);
            if (isset($this->usaStates[$stateCode])) {
                return $this->usaStates[$stateCode];
            }
        }

        return $this->resolveByLatitudeAndLongitude($venue['lat'], $venue['lon']);
    }

    /**
     * @return mixed[]
     */
    private function getCountryJsonByLatitudeAndLongitude(float $latitude, float $longitude): array
    {
        $cacheKey = sha1($longitude . $longitude);
        if (isset($this->countryJsonByLatitudeAndLongitudeCache[$cacheKey])) {
            return $this->countryJsonByLatitudeAndLongitudeCache[$cacheKey];
        }

        $url = sprintf(self::API_LOCATION_TO_COUNTRY, $latitude, $longitude);

        $request = new Request('GET', $url);
        $response = $this->client->send($request);

        // unable to resolve country
        if ($response->getStatusCode() !== 200) {
            throw BadResponseException::create($request, $response);
        }

        $countryJson = $this->responseFormatter->formatResponseToJson($response, $url);

        $this->countryJsonByLatitudeAndLongitudeCache[$cacheKey] = $countryJson;

        return $countryJson;
    }

    /**
     * @see https://stackoverflow.com/a/45826290/1348344
     *
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

        $countryJson = $this->getCountryJsonByLatitudeAndLongitude($group['latitude'], $group['latitude']);

        return $countryJson['address']['country_code'];
    }
}
