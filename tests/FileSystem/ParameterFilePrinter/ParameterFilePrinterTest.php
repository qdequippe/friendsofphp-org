<?php

declare(strict_types=1);

namespace Fop\Core\Tests\FileSystem\ParameterFilePrinter;

use Fop\Core\FileSystem\ParameterFilePrinter;
use Fop\Core\HttpKernel\FopKernel;
use Fop\Meetup\ValueObject\ParameterHolder;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ParameterFilePrinterTest extends AbstractKernelTestCase
{
    private ParameterFilePrinter $parameterFilePrinter;

    private SmartFileSystem $smartFileSystem;

    protected function setUp(): void
    {
        $this->bootKernel(FopKernel::class);
        $this->parameterFilePrinter = $this->getService(ParameterFilePrinter::class);
        $this->smartFileSystem = $this->getService(SmartFileSystem::class);
    }

    public function test(): void
    {
        $parameterHolder = new ParameterHolder('hey', [
            'one' => 'two',
        ]);

        $filePath = __DIR__ . '/Fixture/local_temp_config.php';
        $this->parameterFilePrinter->printParameterHolder($parameterHolder, $filePath);
        $this->assertFileEquals(__DIR__ . '/Fixture/expected_config.php.inc', $filePath);

        // cleanup
        $this->smartFileSystem->remove($filePath);
    }
}
