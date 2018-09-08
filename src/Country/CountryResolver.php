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

    /**
     * @var string
     */
    private const UNKNOWN_COUNTRY = 'unknown';

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
        if (isset($group['country']) && $group['country'] && $group['country'] !== '-') {

            dump($group['country']);

            $country = CountryLoader::country($group['country']);
            if ($country instanceof Country) {
                return $country->getName();
            }
        }

        return $this->getCountryByLatitudeAndLongitude($group['latitude'], $group['longitude']);
    }

    /**
     * @see https://stackoverflow.com/a/45826290/1348344
     */
    private function getCountryByLatitudeAndLongitude(float $latitude, float $longitude): string
    {
        $url = sprintf(
            'https://nominatim.openstreetmap.org/reverse?format=json&lat=%s&lon=%s',
            $latitude,
            $longitude
        );

        $response = $this->client->request('GET', $url);
        // unable to resolve country
        if ($response->getStatusCode() !== 200) {
            return self::UNKNOWN_COUNTRY;
        }

        $json = $this->responseFormatter->formatResponseToJson($response, $url);

        if (isset($json['address']['country'])) {
            return $json['address']['country'];
        }

        if (isset($json['address']['state'])) {
            return $json['address']['state'];
        }

        return self::UNKNOWN_COUNTRY;
    }
}
