<?php

declare(strict_types=1);

namespace Fop\Core\FileSystem;

use Fop\Meetup\ValueObject\ParameterHolder;
use Symplify\PhpConfigPrinter\YamlToPhpConverter;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * @see \Fop\Core\Tests\FileSystem\ParameterFilePrinter\ParameterFilePrinterTest
 */
final class ParameterFilePrinter
{
    public function __construct(
        private SmartFileSystem $smartFileSystem,
        private YamlToPhpConverter $yamlToPhpConverter
    ) {
    }

    public function printParameterHolder(ParameterHolder $parameterHolder, string $filePath): void
    {
        $fileContent = $this->yamlToPhpConverter->convertYamlArray([
            'parameters' => [
                $parameterHolder->getParameterName() => $parameterHolder->getParameterValue(),
            ],
        ]);

        $this->smartFileSystem->dumpFile($filePath, $fileContent);
    }
}
