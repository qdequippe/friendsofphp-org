<?php declare(strict_types=1);

namespace Fop\Tests\Utils;

use Fop\Utils\CityNormalizer;
use Iterator;
use PHPUnit\Framework\TestCase;

final class CityNormalizerTest extends TestCase
{
    /**
     * @var CityNormalizer
     */
    private $cityNormalizer;

    protected function setUp(): void
    {
        $this->cityNormalizer = new CityNormalizer();
    }

    /**
     * @dataProvider provideData()
     */
    public function test(string $invalidCity, string $correctCity): void
    {
        $this->assertSame($correctCity, $this->cityNormalizer->normalize($invalidCity));
    }

    public function provideData(): Iterator
    {
        yield ['Praha', 'Prague'];
        yield ['Praha 7', 'Prague'];
        yield ['Praha-Nové Město', 'Prague'];
        yield ['Brno-Královo Pole', 'Brno'];
        yield ['Brno-střed-Veveří', 'Brno'];
        yield ['132 Budapest', 'Budapest'];
        yield ['Wien', 'Vienna'];
        yield ['1190 Wien', 'Vienna'];
        yield ['Oxford OX2 6AE', 'Oxford'];
        yield ['Oxford OB2 6AE', 'Oxford'];
    }
}
