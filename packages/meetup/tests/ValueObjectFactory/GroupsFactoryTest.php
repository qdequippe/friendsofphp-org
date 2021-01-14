<?php

declare(strict_types=1);

namespace Fop\Meetup\Tests\ValueObjectFactory;

use Fop\Core\HttpKernel\FopKernel;
use Fop\Core\ValueObject\Option;
use Fop\Meetup\ValueObject\Group;
use Fop\Meetup\ValueObjectFactory\GroupsFactory;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class GroupsFactoryTest extends AbstractKernelTestCase
{
    private GroupsFactory $groupsFactory;

    private ParameterProvider $parameterProvider;

    protected function setUp(): void
    {
        $this->bootKernel(FopKernel::class);
        $this->groupsFactory = $this->getService(GroupsFactory::class);
        $this->parameterProvider = $this->getService(ParameterProvider::class);
    }

    public function test(): void
    {
        $groupsArray = $this->parameterProvider->provideArrayParameter(Option::GROUPS);
        $groups = $this->groupsFactory->create($groupsArray);

        $groupCount = count($groups);
        $this->assertGreaterThan(50, $groupCount);
        $this->assertContainsOnlyInstancesOf(Group::class, $groups);
    }
}
