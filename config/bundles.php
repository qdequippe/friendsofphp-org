<?php declare(strict_types=1);

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => [
        'all' => true,
    ],
    Symfony\Bundle\DebugBundle\DebugBundle::class => [
        'dev' => true,
        'test' => true,
    ],
    Symfony\Bundle\TwigBundle\TwigBundle::class => [
        'all' => true,
    ],
    Symplify\ConsoleColorDiff\ConsoleColorDiffBundle::class => [
        'dev' => true,
        'test' => true,
    ],
    Symplify\ComposerJsonManipulator\ComposerJsonManipulatorBundle::class => [
        'dev' => true,
        'test' => true,
    ],
];
