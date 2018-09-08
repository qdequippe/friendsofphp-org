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
     * @dataProvider provideData()
     */
    public function test(string $input, string $expecedCountry): void
    {
        $country = $this->countryResolver->resolveFromGroup([
            'country' => $input,
        ]);

        $this->assertSame($expecedCountry, $country);
    }

    public function provideData(): Iterator
    {
        yield ['CZ', 'Czech Republic'];
    }
}
