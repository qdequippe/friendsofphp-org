<?php declare(strict_types=1);

namespace Fop\Api;

use Nette\Utils\DateTime;
use Symplify\Statie\Contract\Api\ApiItemDecoratorInterface;

final class GroupsApiItemDecorator implements ApiItemDecoratorInterface
{
    public function getName(): string
    {
        return 'groups';
    }

    /**
     * @param mixed[] $items
     * @return mixed[]
     */
    public function decorate(array $items): array
    {
        $groupCount = count($items['groups'] ?? []);

        return array_merge([
            'group_count' => $groupCount,
            'generated_at' => DateTime::from('now')->format('Y-m-d H:i:s'),
        ], $items);
    }
}
