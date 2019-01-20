<?php declare(strict_types=1);

namespace Fop\Repository;

use Fop\Entity\Group;
use Symplify\PackageBuilder\Yaml\ParameterMergingYamlLoader;

final class GroupRepository
{
    /**
     * @var mixed[]
     */
    private $groups = [];

    public function __construct(string $groupsStorage, ParameterMergingYamlLoader $parameterMergingYamlLoader)
    {
        $parameterBag = $parameterMergingYamlLoader->loadParameterBagFromFile($groupsStorage);
        $this->groups = $parameterBag->get('groups');
    }

    /**
     * @return mixed[]
     */
    public function fetchAll(): array
    {
        return $this->groups;
    }

    /**
     * @return mixed[]
     */
    public function findByUrl(string $groupUrl): ?array
    {
        foreach ($this->groups as $group) {
            if (rtrim($group[Group::URL], '/') === rtrim($groupUrl, '/')) {
                return $group;
            }
        }

        return null;
    }

    /**
     * @return int[]
     */
    public function fetchGroupIds(): array
    {
        $groups = $this->fetchAll();

        return array_column($groups, 'meetup_com_id');
    }
}
