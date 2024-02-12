<?php

declare(strict_types=1);

namespace Fop\Utils;

use Fop\Contract\GeocoderInterface;
use Fop\Exception\CoordinateNotFoundForAddressException;
use Location\Coordinate;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class NominatimGeocoder implements GeocoderInterface
{
    private const BASE_URI = 'https://nominatim.openstreetmap.org';

    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {
    }


    public function retrieveCoordinate(string $address): Coordinate
    {
        $response = $this->httpClient->request('GET', sprintf('%s/search', self::BASE_URI), [
            'query' => [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
            ],
        ]);

        $data = $response->toArray();

        if ($data === []) {
            throw CoordinateNotFoundForAddressException::create($address);
        }

        $placeFound = current($data);

        if (isset($placeFound['lat']) === false || isset($placeFound['lon']) === false) {
            throw CoordinateNotFoundForAddressException::create($address);
        }

        return new Coordinate($placeFound['lat'], $placeFound['lon']);
    }
}
