<?php declare(strict_types=1);

namespace Fop\Utils;

final class Arrays
{
    /**
     * @param mixed[] $array
     * @return mixed[]
     */
    public static function unique(array $array): array
    {
        return array_map('unserialize', array_unique(array_map('serialize', $array)));
    }
}
