<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symplify\SymfonyStaticDumper\SymfonyStaticDumperBundle::class => ['all' => true],
    Symplify\EasyHydrator\EasyHydratorBundle::class => ['all' => true],
    Symplify\SimplePhpDocParser\Bundle\SimplePhpDocParserBundle::class => ['all' => true],
    Symplify\PhpConfigPrinter\Bundle\PhpConfigPrinterBundle::class => ['all' => true],
];
