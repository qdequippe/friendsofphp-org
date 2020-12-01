<?php

declare(strict_types=1);

use Symplify\SymfonyStaticDumper\SymfonyStaticDumperBundle;

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    SymfonyStaticDumperBundle::class => ['all' => true],
];
