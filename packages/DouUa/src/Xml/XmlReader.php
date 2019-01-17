<?php declare(strict_types=1);

namespace Fop\DouUa\Xml;

use Fop\DouUa\Exception\XmlException;
use SimpleXMLElement;
use function Safe\sprintf;

final class XmlReader
{
    public function loadFile(string $file): SimpleXMLElement
    {
        $xml = simpleXML_load_file($file);
        if ($xml === false) {
            throw new XmlException(sprintf('Failed to load "%s" file', $file));
        }

        return $xml;
    }
}
