<?php

declare(strict_types=1);

namespace Fop\Core\Twig\Extension;

use Twig\Environment;
use Twig\Extension\AbstractExtension;

final class GlobalCountTwigExtension extends AbstractExtension
{
    public function __construct(Environment $environment, array $groups = [], array $meetups = [])
    {
        $meetupCount = count($meetups);
        $environment->addGlobal('meetup_count', $meetupCount);

        $groupCount = count($groups);
        $environment->addGlobal('group_count', $groupCount);
    }
}
