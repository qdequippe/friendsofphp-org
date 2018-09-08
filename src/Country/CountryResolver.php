<?php declare(strict_types=1);

namespace Fop\Country;

use Fop\Guzzle\ResponseFormatter;
use GuzzleHttp\Client;
use Nette\Utils\Json;
use Rinvex\Country\Country;
use Rinvex\Country\CountryLoader;

final class CountryResolver
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var ResponseFormatter
     */
    private $responseFormatter;

    public function __construct(Client $client, ResponseFormatter $responseFormatter)
    {
        $this->client = $client;
        $this->responseFormatter = $responseFormatter;
    }

    /**
     * @param mixed[] $group
     */
    public function resolveFromGroup(array $group): string
    {
        if (isset($group['country']) && $group['country']) {
            // invalid country
            if ($group['country'] === '-') {
                return 'unknown';
            }

            $country = CountryLoader::country($group['country']);
            if ($country instanceof Country) {
                return $country->getName();
            }
        }

        $geocode = $this->resolveGeocodeFromGoogleMapsByLatitudeAndLongitude($group['latitude'], $group['longitude']);

        $geocodeJson = Json::decode($geocode, Json::FORCE_ARRAY);
        if ($geocodeJson['status'] !== 'OK') {
            return 'unknown';
        }

        foreach ($geocodeJson['results'][0]['address_components'] as $addressComponent) {
            if (in_array('country', $addressComponent['types'], true)) {
                $country = CountryLoader::country($addressComponent['short_name']);
                if ($country instanceof Country) {
                    return $country->getName();
                }
            }
        }

        return 'unknown';
    }

    /**
     * @return bool|string
     */
    private function resolveGeocodeFromGoogleMapsByLatitudeAndLongitude(float $latitude, float $longitude)
    {
        $url = sprintf(
            'http://maps.googleapis.com/maps/api/geocode/json?latlng=%s,%s&sensor=false',
            $latitude,
            $longitude
        );

        $response = $this->client->request('GET', $url);
        $json = $this->responseFormatter->formatResponseToJson($response, $url);

        dump($json);
        die;
    }
}
