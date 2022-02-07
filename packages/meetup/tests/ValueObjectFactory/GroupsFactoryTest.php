<?php

declare(strict_types=1);

namespace Fop\Meetup\Tests\ValueObjectFactory;

use Fop\Core\HttpKernel\FopKernel;
use Fop\Meetup\Repository\GroupRepository;
use Fop\Meetup\ValueObject\Group;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class GroupsFactoryTest extends AbstractKernelTestCase
{
    private GroupRepository $groupRepository;

    protected function setUp(): void
    {
        $this->bootKernel(FopKernel::class);
        $this->groupRepository = $this->getService(GroupRepository::class);
    }

    public function test(): void
    {
        $groups = $this->groupRepository->fetchAll();
        $groupCount = count($groups);

        $this->assertGreaterThan(50, $groupCount);
        $this->assertContainsOnlyInstancesOf(Group::class, $groups);
    }
}
