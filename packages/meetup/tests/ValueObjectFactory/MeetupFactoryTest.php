<?php

declare(strict_types=1);

namespace Fop\Meetup\Tests\ValueObjectFactory;

use Fop\Core\HttpKernel\FopKernel;
use Fop\Core\ValueObject\Option;
use Fop\Meetup\ValueObject\Meetup;
use Fop\Meetup\ValueObjectFactory\MeetupFactory;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class MeetupFactoryTest extends AbstractKernelTestCase
{
    private ParameterProvider $parameterProvider;

    private MeetupFactory $meetupFactory;

    protected function setUp(): void
    {
        $this->bootKernel(FopKernel::class);
        $this->parameterProvider = $this->getService(ParameterProvider::class);
        $this->meetupFactory = $this->getService(MeetupFactory::class);
    }

    public function test(): void
    {
        $meetupsArray = $this->parameterProvider->provideArrayParameter(Option::MEETUPS);
        $meetups = $this->meetupFactory->create($meetupsArray);

        $meetupCount = count($meetups);
        $this->assertGreaterThan(10, $meetupCount);
        $this->assertContainsOnlyInstancesOf(Meetup::class, $meetups);
    }
}
