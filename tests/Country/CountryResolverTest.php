<?php declare(strict_types=1);

namespace Fop\Tests\Country;

use Fop\Country\CountryResolver;
use Fop\Tests\AbstractContainerAwareTestCase;
use PHPUnit\Framework\TestCase;
use Rinvex\Country\Country;

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

    public function test(): void
    {
        $country = $this->countryResolver->resolveFromGroup([
            'country' => 'CZ',
        ]);

        $this->assertInstanceOf(Country::class, $country);

        $this->assertSame('Czech Republic', $country->getName());
    }
}
