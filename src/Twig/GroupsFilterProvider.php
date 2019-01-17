<?php declare(strict_types=1);

namespace Fop\Twig;

use Symplify\Statie\Contract\Templating\FilterProviderInterface;

final class GroupsFilterProvider implements FilterProviderInterface
{
    /**
     * @return callable[]
     */
    public function provide(): array
    {
        return [
            'sortGroupsAndGroupByCountry' => function (array $groups): array {
                $groups = $this->sortGroupsByCountryAndGroupName($groups);

                return $this->groupByCountry($groups);
            },
        ];
    }

    /**
     * @param mixed[] $groups
     * @return mixed[]
     */
    private function sortGroupsByCountryAndGroupName(array $groups): array
    {
        usort($groups, function (array $firstGroup, array $secondGroup) {
            $compareStatus = $firstGroup['country'] <=> $secondGroup['country'];
            if ($compareStatus !== 0) {
                return $compareStatus;
            }

            return $firstGroup['name'] <=> $secondGroup['name'];
        });

        return $groups;
    }

    /**
     * @param mixed[] $groups
     * @return mixed[]
     */
    private function groupByCountry(array $groups): array
    {
        $groupsByCountry = [];
        foreach ($groups as $group) {
            $groupsByCountry[$group['country']][] = $group;
        }

        return $groupsByCountry;
    }
}
