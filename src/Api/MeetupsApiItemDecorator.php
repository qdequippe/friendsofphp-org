<?php declare(strict_types=1);

namespace Fop\Api;

use Nette\Utils\DateTime;
use Symplify\Statie\Contract\Api\ApiItemDecoratorInterface;

final class MeetupsApiItemDecorator implements ApiItemDecoratorInterface
{
    public function getName(): string
    {
        return 'meetups';
    }

    /**
     * @param mixed[] $items
     * @return mixed[]
     */
    public function decorate(array $items): array
    {
        $meetupCount = count($items['meetups'] ?? []);

        return array_merge([
            'meetup_count' => $meetupCount,
            'generated_at' => DateTime::from('now')->format('Y-m-d H:i:s'),
        ], $items);
    }
}
