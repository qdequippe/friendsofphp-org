<?php

declare(strict_types=1);

namespace Fop\Core\Controller;

use Fop\Core\ValueObject\Routing\RouteName;
use Fop\Meetup\Repository\GroupRepository;
use Nette\Utils\DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ApiGroupsController extends AbstractController
{
    public function __construct(
        private readonly GroupRepository $groupRepository
    ) {
    }

    /**
     * Note: beware the dot in the route name! @see https://github.com/symfony/symfony/issues/26099 The first route is
     * for testing locally.
     */
    #[Route('api/groups-json')]
    #[Route('api/groups.json', name: RouteName::API_GROUPS_JSON)]
    public function __invoke(): Response
    {
        $generatedAt = DateTime::from('now')->format('Y-m-d H:i:s');

        $groups = $this->groupRepository->fetchAll();

        return $this->json([
            'generated_at' => $generatedAt,
            'group_count' => count($groups),
            'groups' => $groups,
        ]);
    }
}
