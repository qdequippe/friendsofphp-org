<?php declare(strict_types=1);

namespace Fop\Core\FileSystem;

use Nette\Utils\DateTime;
use Nette\Utils\FileSystem;
use Symfony\Component\Yaml\Yaml;

final class YamlFileSystem
{
    /**
     * @param mixed[] $data
     */
    public function saveArrayToFile(array $data, string $file): void
    {
        $yamlDump = Yaml::dump($data, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        $dateTime = new DateTime();

        $timestampComment = sprintf(
            '# this file was generated on %s, do not edit it manually' . PHP_EOL,
            $dateTime->format('Y-m-d H:i:s')
        );

        FileSystem::write($file, $timestampComment . $yamlDump);
    }
}
