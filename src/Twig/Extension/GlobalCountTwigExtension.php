<?php

declare(strict_types=1);

namespace Fop\Core\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class GlobalCountTwigExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private array $groups = [],
        private array $meetups = []
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function getGlobals(): array
    {
        return [
            'meetup_count' => count($this->meetups),
            'group_count' => count($this->groups),
        ];
    }
}
