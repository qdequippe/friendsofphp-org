<?php

declare(strict_types=1);

namespace Fop\Core\Controller;

use Fop\Core\Templating\ResponseRenderer;
use Fop\Core\ValueObject\Option;
use Fop\Core\ValueObject\Routing\RouteName;
use Nette\Utils\DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class ApiGroupsController
{
    /**
     * @var mixed[]
     */
    private array $groups = [];

    public function __construct(
        ParameterProvider $parameterProvider,
        private ResponseRenderer $responseRenderer
    ) {
        $this->groups = $parameterProvider->provideArrayParameter(Option::GROUPS);
    }

    /**
     * Note: beware the dot in the route name! @see https://github.com/symfony/symfony/issues/26099
     */

    #[Route('api/groups.json', name: RouteName::API_GROUPS_JSON)]
    public function __invoke(): Response
    {
        $generatedAt = DateTime::from('now')->format('Y-m-d H:i:s');

        return $this->responseRenderer->json([
            'generated_at' => $generatedAt,
            'group_count' => count($this->groups),
            'groups' => $this->groups,
        ]);
    }
}
