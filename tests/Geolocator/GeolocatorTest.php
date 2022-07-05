<?php

declare(strict_types=1);

namespace Fop\Tests\Geolocator;

use Fop\Geolocation\Geolocator;
use Fop\HttpKernel\FopKernel;
use Iterator;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class GeolocatorTest extends AbstractKernelTestCase
{
    private Geolocator $geolocator;

    protected function setUp(): void
    {
        $this->bootKernel(FopKernel::class);
        $this->geolocator = $this->getService(Geolocator::class);
    }

    /**
     * @param mixed[] $group
     * @dataProvider provideData()
     */
    public function test(array $group, string $expectedCountry): void
    {
        $resolvedCountry = $this->geolocator->resolveCountryByGroup($group);
        $this->assertSame($expectedCountry, $resolvedCountry);
    }

    /**
     * @return Iterator<mixed[]>
     */
    public function provideData(): Iterator
    {
        yield [[
            'country' => 'CZ',
        ], 'Czech Republic'];

        yield [[
            'latitude' => 50.847572953654,
            'longitude' => 4.3535041809082,
        ], 'Belgium'];
    }
}
