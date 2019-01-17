<?php declare(strict_types=1);

namespace Fop\Tests\Geolocator;

use Fop\Geolocation\Geolocator;
use Fop\Tests\AbstractContainerAwareTestCase;
use Iterator;

final class GeolocatorTest extends AbstractContainerAwareTestCase
{
    /**
     * @var Geolocator
     */
    private $geolocator;

    protected function setUp(): void
    {
        $this->geolocator = $this->container->get(Geolocator::class);
    }

    /**
     * @param mixed[] $group
     * @dataProvider provideData()
     */
    public function test(array $group, string $expecedCountry): void
    {
        $this->assertSame($expecedCountry, $this->geolocator->resolveCountryByGroup($group));
    }

    public function provideData(): Iterator
    {
        yield [['country' => 'CZ'], 'Czech Republic'];
        yield [[
            'latitude' => 50.847572953654,
            'longitude' => 4.3535041809082,
        ], 'Belgium'];
    }
}
