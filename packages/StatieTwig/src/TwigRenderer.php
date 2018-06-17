<?php declare(strict_types=1);

namespace Statie\StatieTwig;

final class TwigRenderer
{
    public function __construct()
    {
    }

    public function render(string $content): string
    {
        dump($content);
        die;
    }
}
