<?php declare(strict_types=1);

namespace Fop\Repository;

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
     * @return string[]
     */
    public function fetchGroupSlugs(): array
    {
        $groups = $this->fetchAll();

        return array_column($groups, 'meetup_com_slug');
    }
}
