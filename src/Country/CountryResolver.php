<?php declare(strict_types=1);

namespace Fop\Country;

use Nette\Utils\Json;
use Rinvex\Country\Country;
use Rinvex\Country\CountryLoader;

final class CountryResolver
{
    /**
     * @param mixed[] $group
     */
    public function resolveFromGroup(array $group): ?Country
    {
        if (isset($group['country']) && $group['country']) {
            return CountryLoader::country($group['country']);
        }

        $geocode = $this->resolveGeocodeFromGoogleMapsByLatitudeAndLongitude($group['latitude'], $group['longitude']);

        $geocodeJson = Json::decode($geocode, Json::FORCE_ARRAY);
        if ($geocodeJson['status'] !== 'OK') {
            return null;
        }

        foreach ($geocodeJson['results'][0]['address_components'] as $addressComponent) {
            if (in_array('country', $addressComponent['types'], true)) {
                return CountryLoader::country($addressComponent['short_name']);
            }
        }

        return null;
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

        return file_get_contents($url);
    }
}
