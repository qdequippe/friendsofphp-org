<?php declare(strict_types=1);

namespace Fop\Twig;

use Snilius\Twig\SortByFieldExtension;
use Symplify\Statie\Contract\Templating\FilterProviderInterface;

final class SortingFilterProvider implements FilterProviderInterface
{
    /**
     * @var SortByFieldExtension
     */
    private $sortByFieldExtension;

    public function __construct(SortByFieldExtension $sortByFieldExtension)
    {
        $this->sortByFieldExtension = $sortByFieldExtension;
    }

    /**
     * @return callable[]
     */
    public function provide(): array
    {
        return [
            'sortByField' => function (array $items, $sortBy = null, $direction = 'asc'): array {
                return $this->sortByFieldExtension->sortByFieldFilter($items, $sortBy, $direction);
            },
        ];
    }
}
