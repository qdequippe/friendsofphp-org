<?php

use Symplify\SimplePhpDocParser\Bundle\SimplePhpDocParserBundle;

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symplify\SymfonyStaticDumper\SymfonyStaticDumperBundle::class => ['all' => true],
    Symplify\EasyHydrator\EasyHydratorBundle::class => ['all' => true],
    SimplePhpDocParserBundle::class => ['all' => true],
];
