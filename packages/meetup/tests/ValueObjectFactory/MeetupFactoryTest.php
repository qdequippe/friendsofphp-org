<?php

declare(strict_types=1);

namespace Fop\Meetup\Tests\ValueObjectFactory;

use Fop\Core\HttpKernel\FopKernel;
use Fop\Meetup\Repository\MeetupRepository;
use Fop\Meetup\ValueObject\Meetup;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class MeetupFactoryTest extends AbstractKernelTestCase
{
    private MeetupRepository $meetupRepository;

    protected function setUp(): void
    {
        $this->bootKernel(FopKernel::class);
        $this->meetupRepository = $this->getService(MeetupRepository::class);
    }

    public function test(): void
    {
        $meetups = $this->meetupRepository->fetchAll();

        $meetupCount = count($meetups);
        $this->assertGreaterThan(10, $meetupCount);
        $this->assertContainsOnlyInstancesOf(Meetup::class, $meetups);
    }
}
