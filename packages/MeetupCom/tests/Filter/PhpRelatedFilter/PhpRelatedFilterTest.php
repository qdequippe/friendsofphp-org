<?php declare(strict_types=1);

namespace Fop\MeetupCom\Tests\Filter\PhpRelatedFilter;

use Fop\MeetupCom\Filter\PhpRelatedFilter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class PhpRelatedFilterTest extends TestCase
{
    /**
     * @var PhpRelatedFilter
     */
    private $phpRelatedFilter;

    protected function setUp(): void
    {
        $this->phpRelatedFilter = new PhpRelatedFilter();
    }

    public function test(): void
    {
        $groupsToEvaluate = Yaml::parseFile(__DIR__ . '/Source/groups_input.yml');
        $filteredGroups = $this->phpRelatedFilter->filterGroups($groupsToEvaluate['groups']);

        $this->assertCount(91, $filteredGroups);
    }
}
