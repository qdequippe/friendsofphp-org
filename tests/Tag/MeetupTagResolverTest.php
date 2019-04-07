<?php declare(strict_types=1);

namespace Fop\Tests\Tag;

use Fop\Tag\MeetupTagResolver;
use Iterator;
use PHPUnit\Framework\TestCase;

final class MeetupTagResolverTest extends TestCase
{
    /**
     * @var MeetupTagResolver
     */
    private $meetupTagResolver;

    protected function setUp(): void
    {
        $this->meetupTagResolver = new MeetupTagResolver();
    }

    /**
     * @dataProvider provideData
     * @param string[] $resolvedTags
     */
    public function test(string $name, string $groupName, array $resolvedTags): void
    {
        $this->assertSame($resolvedTags, $this->meetupTagResolver->resolveFromName($name, $groupName));
    }

    public function provideData(): Iterator
    {
        # meetup
        yield ['Welcome to Drupal', '', ['drupal']];
        yield ['WP meetup', '', ['wordpress']];
        yield ['[North Lakes] Brisbane Northside WordPress Meetup', '', ['wordpress']];
        # group
        yield ['Another meetup', 'Mumbai WordPress Meetup', ['wordpress']];
    }
}
