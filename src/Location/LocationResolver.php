<?php declare(strict_types=1);

namespace Fop\Location;

use Fop\Country\CountryResolver;
use Fop\Entity\Location;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Location\Coordinate;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

final class LocationResolver
{
    /**
     * @var string
     */
    private const API_CITY_TO_LOCATION = 'https://nominatim.openstreetmap.org/search.php?q=%s&format=json';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var CountryResolver
     */
    private $countryResolver;

    public function __construct(Client $client, CountryResolver $countryResolver)
    {
        $this->client = $client;
        $this->countryResolver = $countryResolver;
    }

    public function createFromCity(string $city): ?Location
    {
        $url = sprintf(self::API_CITY_TO_LOCATION, $city);

        $request = new Request('GET', $url);
        $response = $this->client->send($request);

        $responseBody = (string) $response->getBody();

        try {
            $json = Json::decode($responseBody, Json::FORCE_ARRAY);
        } catch (JsonException $jsonException) {
            // invalid response
            return null;
        }

        if (! isset($json[0]['lat']) || ! isset($json[0]['lat'])) {
            return null;
        }

        $lat = (float) $json[0]['lat'];
        $lon = (float) $json[0]['lon'];

        $country = $this->countryResolver->resolveByLatitudeAndLongitude($lat, $lon);
        return new Location($city, $country, new Coordinate($lat, $lon));
    }
}
