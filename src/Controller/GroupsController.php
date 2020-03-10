<?php

declare(strict_types=1);

namespace Fop\Controller;

use Fop\Group\Repository\GroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GroupsController extends AbstractController
{
    /**
     * @var GroupRepository
     */
    private $groupRepository;

    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    /**
     * @Route(path="groups", name="groups")
     */
    public function __invoke(): Response
    {
        return $this->render('groups.twig', [
            'groups' => $this->groupRepository->fetchAll(),
            'groups_by_country' => $this->groupRepository->fetchGroupedByCountry(),
        ]);
    }
}
