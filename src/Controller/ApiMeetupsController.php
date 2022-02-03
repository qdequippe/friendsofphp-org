<?php

declare(strict_types=1);

namespace Fop\Core\Controller;

use Fop\Core\ValueObject\Routing\RouteName;
use Fop\Meetup\Repository\MeetupRepository;
use Nette\Utils\DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ApiMeetupsController extends AbstractController
{
    public function __construct(
        private readonly MeetupRepository $meetupRepository,
    ) {
    }

    /**
     * Note: beware the dot in the route name! @see https://github.com/symfony/symfony/issues/26099 The first route is
     * for testing locally.
     */
    #[Route('api/meetups-json')]
    #[Route('api/meetups.json', name: RouteName::API_MEETUPS_JSON)]
    public function __invoke(): Response
    {
        $generatedAt = DateTime::from('now')->format('Y-m-d H:i:s');

        return $this->json([
            'generated_at' => $generatedAt,
            'meetups' => $this->meetupRepository->fetchAll(),
        ]);
    }
}
