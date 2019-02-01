<?php declare(strict_types=1);

namespace Fop\Utils;

/**
 * Convert non-standard names to one format
 */
final class CityNormalizer
{
    /**
     * @var string[]
     */
    private $cityNormalizationMap = [
        'Praha-Nové Město' => 'Prague',
        'Praha' => 'Prague',
        'Brno-Královo Pole' => 'Brno',
        'Brno-střed-Veveří' => 'Brno',
        'Hlavní město Praha' => 'Prague',
        '1065 Budapest' => 'Budapest',
        'ISTANBUL' => 'Istanbul',
        'Wien' => 'Vienna',
        '1190 Wien' => 'Vienna',
        '8000 Aarhus C' => 'Aarhus',
        'Le Kremlin-Bicêtre' => 'Paris',
        'Parramatta' => 'Paris',
        'Stellenbosch' => 'Cape Town',
        '台北' => 'Taipei',
        'New Taipei City' => 'Taipei',
        # Japan
        '東京都' => 'Tokyo',
        '愛知県' => 'Aichi Prefecture',
        '兵庫県' => 'Hyōgo',
        '長野県松本市' => 'Matsumoto',
        'Tōkyō-to ' => 'Tokyo',
        # Germany
        'Köln' => 'Cologne',
        '10997 Berlin' => 'Berlin',
        '22765 Hamburg' => 'Hamburg',
        '76227 Karlsruhe' => 'Karlsruhe',
        'Unterföhrin' => 'Munich',
        # UK
        'G1 1TF' => 'Glasgow',
        'EC2A 2BA' => 'London',
        'London, EC2Y 9AE' => 'London',
        'Oxford OX1 3BY' => 'Oxford',
        'Oxford OX2 6AE' => 'Oxford',
        'Reading RG1 1DG' => 'Reading',
        'M4 2AH' => 'Manchester',
        'BH12 1AZ' => 'Poole',
        'LE2 7DR' => 'Leicester',
        'BS2 0BY' => 'Bristol',
    ];

    public function normalize(string $city): string
    {
        return $this->cityNormalizationMap[$city] ?? $city;
    }
}
