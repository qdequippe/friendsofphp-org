<?php

declare(strict_types=1);

namespace Fop\Core\Controller;

use Fop\Core\Templating\ResponseRenderer;
use Fop\Core\ValueObject\Routing\RouteName;
use Fop\Meetup\Repository\MeetupRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomepageController
{
    public function __construct(
        private MeetupRepository $meetupRepository,
        private ResponseRenderer $responseRenderer
    ) {
    }

    #[Route('/', name: RouteName::HOMEPAGE)]
    public function __invoke(): Response
    {
        return $this->responseRenderer->render('index.twig', [
            'meetups' => $this->meetupRepository->fetchAll(),
        ]);
    }
}
