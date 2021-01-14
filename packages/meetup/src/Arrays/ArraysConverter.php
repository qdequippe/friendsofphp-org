<?php

declare(strict_types=1);

namespace Fop\Meetup\Arrays;

use Fop\Meetup\Contract\ArrayableInterface;

final class ArraysConverter
{
    /**
     * @param ArrayableInterface[] $items
     * @return mixed[]
     */
    public function turnToArrays(array $items): array
    {
        $itemsArray = [];
        foreach ($items as $item) {
            $itemsArray[] = $item->toArray();
        }

        return $itemsArray;
    }
}
