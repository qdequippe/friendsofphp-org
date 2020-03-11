<?php

declare(strict_types=1);

namespace Fop\Core\Controller;

use Nette\Utils\DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ApiMeetupsController extends AbstractController
{
    /**
     * @var mixed[]
     */
    private $meetups = [];

    public function __construct(array $meetups = [])
    {
        $this->meetups = $meetups;
    }

    /**
     * Note: beware the dot in the route name! @see https://github.com/symfony/symfony/issues/26099
     * @Route(path="api/meetups.json", name="api_meetups_json", methods={"GET"})
     */
    public function __invoke(): Response
    {
        $generatedAt = DateTime::from('now')->format('Y-m-d H:i:s');

        return $this->json([
            'generated_at' => $generatedAt,
            'meetup_count' => count($this->meetups),
            'meetups' => $this->meetups,
        ]);
    }
}
