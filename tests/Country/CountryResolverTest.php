<?php declare(strict_types=1);

namespace Fop\Tests\Country;

use Fop\Country\CountryResolver;
use Fop\Tests\AbstractContainerAwareTestCase;
use Iterator;

final class CountryResolverTest extends AbstractContainerAwareTestCase
{
    /**
     * @var CountryResolver
     */
    private $countryResolver;

    protected function setUp(): void
    {
        $this->countryResolver = $this->container->get(CountryResolver::class);
    }

    /**
     * @param mixed[] $group
     * @dataProvider provideData()
     */
    public function test(array $group, string $expecedCountry): void
    {
        $this->assertSame($expecedCountry, $this->countryResolver->resolveFromGroup($group));
    }

    public function provideData(): Iterator
    {
        yield [['country' => 'CZ'], 'Czech Republic'];
        yield [[
            'latitude' => 50.847572953654,
            'longitude' => 4.3535041809082,
        ], 'Belgium'];
        yield [['country' => 'random'], 'unknown'];
    }
}
