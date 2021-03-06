<?php

declare(strict_types=1);

namespace Fop\Core\Controller;

use Fop\Core\Templating\ResponseRenderer;
use Fop\Core\ValueObject\Routing\RouteName;
use Fop\Meetup\Repository\MeetupRepository;
use Nette\Utils\DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ApiMeetupsController
{
    public function __construct(
        private MeetupRepository $meetupRepository,
        private ResponseRenderer $responseRenderer
    ) {
    }

    /**
     * Note: beware the dot in the route name! @see https://github.com/symfony/symfony/issues/26099
     */

    #[Route('api/meetups.json', name: RouteName::API_MEETUPS_JSON)]
    public function __invoke(): Response
    {
        $generatedAt = DateTime::from('now')->format('Y-m-d H:i:s');

        return $this->responseRenderer->json([
            'generated_at' => $generatedAt,
            'meetups' => $this->meetupRepository->fetchAll(),
        ]);
    }
}
