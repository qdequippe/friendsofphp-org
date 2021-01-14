<?php

declare(strict_types=1);

namespace Fop\Core\Twig\Extension;

use Fop\Meetup\Repository\GroupRepository;
use Fop\Meetup\Repository\MeetupRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class GlobalCountTwigExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private MeetupRepository $meetupRepository,
        private GroupRepository $groupRepository
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function getGlobals(): array
    {
        return [
            'meetup_count' => $this->meetupRepository->getCount(),
            'group_count' => $this->groupRepository->getCount(),
        ];
    }
}
