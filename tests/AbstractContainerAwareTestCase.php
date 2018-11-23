<?php declare(strict_types=1);

namespace Fop\Tests;

use Fop\DependencyInjection\ContainerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

abstract class AbstractContainerAwareTestCase extends TestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param mixed[] $data
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        $this->container = (new ContainerFactory())->createWithConfig(__DIR__ . '/../friends-of-php.yml');

        parent::__construct($name, $data, $dataName);
    }
}
