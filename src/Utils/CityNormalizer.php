<?php

declare(strict_types=1);

namespace Fop\Core\Utils;

use Nette\Utils\Strings;

/**
 * Convert non-standard names to one format
 */
final class CityNormalizer
{
    /**
     * @var string[]
     */
    private array $cityNormalizationMap = [
        '#Praha(.*?)#' => 'Prague',
        '#Brno(.*?)#' => 'Brno',
        'Hlavní město Praha' => 'Prague',
        '#(.*?) Budapest#' => 'Budapest',
        'ISTANBUL' => 'Istanbul',
        '#(.*?)Wien#' => 'Vienna',
        '8000 Aarhus C' => 'Aarhus',
        'Le Kremlin-Bicêtre' => 'Paris',
        'Parramatta' => 'Paris',
        'Stellenbosch' => 'Cape Town',
        '台北' => 'Taipei',
        'New Taipei City' => 'Taipei',
        'харьков' => 'Kharkiv',
        # Japan
        '東京都' => 'Tokyo',
        '愛知県' => 'Aichi Prefecture',
        '兵庫県' => 'Hyōgo',
        '長野県松本市' => 'Matsumoto',
        'Tōkyō-to ' => 'Tokyo',
        # Germany
        'Köln' => 'Cologne',
        '#(.*?) Berlin#' => 'Berlin',
        '#(.*?) Hamburg#' => 'Hamburg',
        '#(.*?) Karlsruhe#' => 'Karlsruhe',
        'Unterföhrin' => 'Munich',
        'München ' => 'Munich',
        # UK
        'G1 1TF' => 'Glasgow',
        'EC2A 2BA' => 'London',
        '#London( |-)(.*?)#' => 'London',
        '#Oxford( |-)(.*?)#' => 'Oxford',
        '#Reading( |-)(.*?)#' => 'Reading',
        'M4 2AH' => 'Manchester',
        'BH12 1AZ' => 'Poole',
        'LE2 7DR' => 'Leicester',
        'BS2 0BY' => 'Bristol',
    ];

    public function normalize(string $city): string
    {
        foreach ($this->cityNormalizationMap as $pattern => $correct) {
            if (Strings::match($city, $this->normalizePattern($pattern))) {
                return $correct;
            }
        }

        return $city;
    }

    private function normalizePattern(string $pattern): string
    {
        if ($pattern[0] === '#') {
            return $pattern;
        }

        return '#' . preg_quote($pattern) . '#';
    }
}
