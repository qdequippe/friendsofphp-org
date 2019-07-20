<?php declare(strict_types=1);

namespace Fop\Twig\Autocomplete;

use DateTimeInterface;

/**
 * Dummy value object to enable autocomplete in *.twig templates
 *
 * @property-read DateTimeInterface $start
 * @property-read float $latitude
 * @property-read float $longitude
 * @property-read string $country
 * @property-read string $city
 * @property-read string $url
 * @property-read string $name
 * @property-read string $user_group
 */
final class MeetupAutocomplete
{
}
