<?php

declare(strict_types=1);

namespace Fop\Core\Twig\Extension;

use Nette\Utils\Strings;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class WebalizeTwigExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): iterable
    {
        $webalizeTwigFilter = new TwigFilter('webalize', function (string $value) {
            return Strings::webalize($value);
        });

        return [$webalizeTwigFilter];
    }
}
