<?php

declare(strict_types=1);

namespace Fop\Twig\Extension;

use Twig\Environment;
use Twig\Extension\AbstractExtension;

final class GlobalCountTwigExtension extends AbstractExtension
{
    public function __construct(Environment $environment, array $groups = [], array $meetups = [])
    {
        $environment->addGlobal('meetup_count', count($meetups));
        $environment->addGlobal('group_count', count($groups));
    }
}
