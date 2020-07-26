<?php declare(strict_types=1);

use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symplify\ConsoleColorDiff\ConsoleColorDiffBundle;

return [
    FrameworkBundle::class => [
        'all' => true,
    ],
    DebugBundle::class => [
        'dev' => true,
        'test' => true,
    ],
    TwigBundle::class => [
        'all' => true,
    ],
    ConsoleColorDiffBundle::class => [
        'dev' => true,
        'test' => true,
    ],
];
