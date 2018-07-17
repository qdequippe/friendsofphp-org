<?php declare(strict_types=1);

namespace Fop\Tests\Country;

use Fop\Country\CountryResolver;
use PHPUnit\Framework\TestCase;
use Rinvex\Country\Country;

final class CountryResolverTest extends TestCase
{
    /**
     * @var CountryResolver
     */
    private $countryResolver;

    protected function setUp(): void
    {
        $this->countryResolver = new CountryResolver();
    }

    public function test(): void
    {
        $country = $this->countryResolver->resolveFromGroup([
            'country' => 'CZ',
        ]);

        $this->assertInstanceOf(Country::class, $country);

        $this->assertSame('Czech Republic', $country->getName());
    }
}
