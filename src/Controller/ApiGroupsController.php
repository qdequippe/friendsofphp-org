<?php

declare(strict_types=1);

namespace Fop\Controller;

use Fop\Meetup\Repository\GroupRepository;
use Fop\ValueObject\Routing\RouteName;
use Nette\Utils\DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    #[Route(path: 'api/groups-json')]
    #[Route(path: 'api/groups.json', name: RouteName::API_GROUPS_JSON)]
    public function __invoke(): JsonResponse
    {
        $generatedAt = DateTime::from('now')->format('Y-m-d H:i:s');

        $groups = $this->groupRepository->fetchAll();

        return $this->json([
            'generated_at' => $generatedAt,
            'groups' => $groups,
        ]);
    }
}
