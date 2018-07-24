<?php declare(strict_types=1);

namespace Fop\Tests\Api;

use Fop\Api\PhpUgApi;
use Fop\Tests\AbstractContainerAwareTestCase;

final class PhpUgApiTest extends AbstractContainerAwareTestCase
{
    /**
     * @var PhpUgApi
     */
    private $phpUgApi;

    protected function setUp(): void
    {
        $this->phpUgApi = $this->container->get(PhpUgApi::class);
    }

    public function test(): void
    {
        $this->assertGreaterThan(30, $this->phpUgApi->getAllGroups());
    }
}
