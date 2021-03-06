<?php

declare(strict_types=1);

namespace Fop\Core\Xml;

use Fop\Core\Exception\XmlException;
use Nette\Utils\Strings;
use SimpleXMLElement;
use Symplify\SmartFileSystem\SmartFileSystem;

final class XmlReader
{
    public function __construct(
        private SmartFileSystem $smartFileSystem
    ) {
    }

    public function loadFile(string $file): SimpleXMLElement
    {
        $fileContent = $this->smartFileSystem->readFile($file);
        $fileContent = $this->correctSyntax($fileContent);

        $xml = simplexml_load_string($fileContent);
        if ($xml === false) {
            throw new XmlException(sprintf('Failed to load "%s" xml file', $file));
        }

        return $xml;
    }

    /**
     * Escape broken syntax - https://stackoverflow.com/a/23422397/1348344
     */
    private function correctSyntax(string $content): string
    {
        return Strings::replace($content, '#&#', '&amp;');
    }
}
