<?php

declare(strict_types=1);

namespace Fop\Twig\Extension;

use Fop\Meetup\Repository\GroupRepository;
use Fop\Meetup\Repository\MeetupRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class GlobalCountTwigExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly MeetupRepository $meetupRepository,
        private readonly GroupRepository $groupRepository
    ) {
    }

    /**
     * @return array{meetup_count: int, group_count: int}
     */
    public function getGlobals(): array
    {
        return [
            'meetup_count' => $this->meetupRepository->getCount(),
            'group_count' => $this->groupRepository->getCount(),
        ];
    }
}
