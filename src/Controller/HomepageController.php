<?php

declare(strict_types=1);

namespace Fop\Core\Controller;

use Fop\Meetup\Repository\MeetupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomepageController extends AbstractController
{
    /**
     * @var MeetupRepository
     */
    private $meetupRepository;

    public function __construct(MeetupRepository $meetupRepository)
    {
        $this->meetupRepository = $meetupRepository;
    }

    /**
     * @Route(path="/", name="homepage")
     */
    public function __invoke(): Response
    {
        return $this->render('index.twig', [
            'meetups' => $this->meetupRepository->fetchAll(),
        ]);
    }
}
